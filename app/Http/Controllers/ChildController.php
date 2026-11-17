<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChildController extends Controller
{
    /** Folder (relative to /public) where child photos are stored. */
    private const PHOTO_DIR = 'uploads/children';

    /** Stored photos are downscaled so the longest side is at most this many px. */
    private const PHOTO_MAX_SIDE = 1200;

    /** Reject images above this many pixels (decompression-bomb / memory-exhaustion guard). */
    private const PHOTO_MAX_PIXELS = 16000000;

    /** Allowed MIME types (detected from file CONTENT, never from the client) => stored extension. */
    private const PHOTO_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Child::class);

        $query = Child::with('employee');

        if (Auth::user()->isParent()) {
            $employee = Employee::where('user_id', Auth::id())->first();

            $employee
                ? $query->where('employee_id', $employee->id)
                : $query->whereRaw('1 = 0');
        }

        if ($request->filled('search')) {
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->string('search')->limit(100)->toString());
            $query->where('name', 'like', '%' . $search . '%');
        }

        $children = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('children.index', compact('children'));
    }

    public function create()
    {
        Gate::authorize('create', Child::class);

        $employees = Employee::orderBy('name')->get();

        return view('children.create', compact('employees'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Child::class);

        $validated = $request->validate($this->rules());

        unset($validated['photo']);

        $newPhoto = null;

        if ($request->hasFile('photo')) {
            try {
                $newPhoto = $this->storePhoto($request->file('photo'));
            } catch (Throwable $e) {
                report($e);

                return back()->withInput()
                    ->withErrors(['photo' => 'Photo upload failed. Please try again with a valid JPG, PNG or WebP image.']);
            }

            $validated['photo'] = $newPhoto;
        }

        try {
            Child::create($validated);
        } catch (Throwable $e) {
            // Don't leave an orphan file behind if the DB write fails.
            $this->deletePhoto($newPhoto);

            throw $e;
        }

        return redirect()->route('children.index')
            ->with('success', 'Child enrolled successfully.');
    }

    public function show(Child $child)
    {
        $this->authorize('view', $child);

        $child->load([
            'employee',
            'emergencyContacts',
            'healthRecord',
            'attendances'
        ]);

        return view('children.show', compact('child'));
    }

    public function edit(Child $child)
    {
        Gate::authorize('update', $child);

        $employees = Employee::orderBy('name')->get();

        return view('children.edit', compact('child', 'employees'));
    }

    public function update(Request $request, Child $child)
    {
        Gate::authorize('update', $child);

        $validated = $request->validate($this->rules() + [
            'status' => 'required|in:active,inactive',
        ]);

        unset($validated['photo']);

        $oldPhoto = $child->photo;
        $newPhoto = null;

        if ($request->hasFile('photo')) {
            try {
                $newPhoto = $this->storePhoto($request->file('photo'));
            } catch (Throwable $e) {
                report($e);

                return back()->withInput()
                    ->withErrors(['photo' => 'Photo upload failed. Please try again with a valid JPG, PNG or WebP image.']);
            }

            $validated['photo'] = $newPhoto;
        }

        try {
            $child->update($validated);
        } catch (Throwable $e) {
            $this->deletePhoto($newPhoto);

            throw $e;
        }

        // Remove the old file only after the DB points to the new one.
        if ($newPhoto) {
            $this->deletePhoto($oldPhoto);
        }

        return redirect()->route('children.index')
            ->with('success', 'Child record updated.');
    }

    public function destroy(Child $child)
    {
        Gate::authorize('delete', $child);

        $photo = $child->photo;

        $child->delete();

        $this->deletePhoto($photo);

        return redirect()->route('children.index')
            ->with('success', 'Child record removed.');
    }

    protected function rules(): array
    {
        return [
            'employee_id'   => 'required|integer|exists:employees,id',
            'name'          => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:today|after:1990-01-01',
            'gender'        => 'nullable|in:male,female,other',
            'photo'         => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048|dimensions:max_width=4000,max_height=4000',
            'notes'         => 'nullable|string|max:2000',
        ];
    }

    /**
     * Validate, sanitise and store an uploaded photo under public/uploads/children.
     *
     * Security measures:
     *  - type is decided from the file's real content (getimagesize), not the client's name/MIME
     *  - stored name is a random UUID + extension chosen by us (no user-controlled filename/path)
     *  - image is decoded and re-encoded with GD, which strips EXIF/GPS and any appended
     *    or polyglot payload (e.g. PHP code hidden inside an image)
     *  - pixel-count cap before decoding to prevent memory exhaustion
     *
     * @return string path relative to /public, e.g. "uploads/children/<uuid>.jpg"
     */
    protected function storePhoto(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new RuntimeException('Upload is not valid.');
        }

        $source = $file->getRealPath();
        $info   = @getimagesize($source);

        if ($info === false || !isset(self::PHOTO_TYPES[$info['mime']])) {
            throw new RuntimeException('Uploaded file is not an allowed image type.');
        }

        [$width, $height] = $info;
        $mime = $info['mime'];

        if ($width < 1 || $height < 1 || ($width * $height) > self::PHOTO_MAX_PIXELS) {
            throw new RuntimeException('Image dimensions are not allowed.');
        }

        $dir = public_path(self::PHOTO_DIR);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create upload directory.');
        }

        $filename = Str::uuid()->toString() . '.' . self::PHOTO_TYPES[$mime];
        $target   = $dir . DIRECTORY_SEPARATOR . $filename;

        if (extension_loaded('gd')) {
            if (!$this->reencodeImage($source, $mime, $target)) {
                throw new RuntimeException('Could not process image.');
            }
        } else {
            // GD missing: still safe-named and content-type checked, but not re-encoded.
            report(new RuntimeException('GD extension missing: photo stored without re-encoding.'));
            $file->move($dir, $filename);
        }

        @chmod($target, 0644);

        return self::PHOTO_DIR . '/' . $filename;
    }

    /** Decode -> (rotate by EXIF) -> (downscale) -> encode to $target. Strips all metadata. */
   protected function reencodeImage(string $source, string $mime, string $target): bool
   {
       $image = false;

       if ($mime === 'image/jpeg') {
           $image = @imagecreatefromjpeg($source);
       } elseif ($mime === 'image/png') {
           $image = @imagecreatefrompng($source);
       } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
           $image = @imagecreatefromwebp($source);
       }

       if (!$image) {
           return false;
       }

       // Phones store rotation in EXIF; apply it before the metadata is dropped.
       if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
           $exif        = @exif_read_data($source);
           $orientation = (is_array($exif) && isset($exif['Orientation'])) ? (int) $exif['Orientation'] : 1;

           $angle = 0;
           if ($orientation === 3) {
               $angle = 180;
           } elseif ($orientation === 6) {
               $angle = -90;
           } elseif ($orientation === 8) {
               $angle = 90;
           }

           if ($angle !== 0) {
               $rotated = imagerotate($image, $angle, 0);
               if ($rotated) {
                   $image = $rotated;
               }
           }
       }

       $w = imagesx($image);
       $h = imagesy($image);

       if (max($w, $h) > self::PHOTO_MAX_SIDE) {
           $ratio  = self::PHOTO_MAX_SIDE / max($w, $h);
           $scaled = imagescale($image, max(1, (int) round($w * $ratio)), max(1, (int) round($h * $ratio)));

           if (!$scaled) {
               return false;
           }

           $image = $scaled;
       }

       if ($mime !== 'image/jpeg') {
           imagealphablending($image, false);
           imagesavealpha($image, true);
       }

       if ($mime === 'image/jpeg') {
           return imagejpeg($image, $target, 85);
       }
       if ($mime === 'image/png') {
           return imagepng($image, $target, 6);
       }
       if ($mime === 'image/webp') {
           return imagewebp($image, $target, 80);
       }

       return false;
   }

    /**
     * Delete a stored photo. The DB value is never trusted blindly: it must match our
     * exact naming pattern and resolve inside the uploads folder (path-traversal guard).
     */
    protected function deletePhoto(?string $relativePath): void
    {
        if (!$relativePath || !Child::isManagedPhotoPath($relativePath)) {
            return; // empty, legacy, or unexpected value: leave alone
        }

        try {
            $base = realpath(public_path(self::PHOTO_DIR));
            $full = realpath(public_path($relativePath));

            if ($base === false || $full === false
                || !str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
                return;
            }

            File::delete($full);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
