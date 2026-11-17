<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Employee;
use App\Models\Staff;
use App\Support\Sanitize;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public const FILTER_RULES = [
        'date'              => 'nullable|string|max:10',
        'name'              => 'nullable|string|max:100',
        'employee_id'       => 'nullable|integer',
        'gender'            => 'nullable|in:male,female,other',
        'attendance_status' => 'nullable|in:not_checked_in,checked_in,checked_out,still_inside',
    ];

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Attendance::class);

        $request->validate(self::FILTER_RULES);

        $date     = $this->resolveDate($request);
        $children = $this->filteredQuery($request, $date)->orderBy('name')->get();

        // Parents must not see the list of other employees/parents.
        $employees = Auth::user()->isParent()
            ? collect([Auth::user()->employee])->filter()
            : Employee::orderBy('name')->get();

        return view('attendance.index', compact('children', 'date', 'employees'));
    }

    /**
     * Shared by the page and the PDF. Includes the parent restriction.
     */
    public function filteredQuery(Request $request, string $date)
    {
        $query = Child::where('status', 'active')->with([
            'employee',
            'attendances' => fn ($q) => $q->whereDate('date', $date),
        ]);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . Sanitize::like($request->input('name')) . '%');
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        switch ($request->input('attendance_status')) {
            case 'not_checked_in':
                $query->whereDoesntHave('attendances', fn ($q) => $q->whereDate('date', $date)->whereNotNull('check_in'));
                break;
            case 'checked_in':
                $query->whereHas('attendances', fn ($q) => $q->whereDate('date', $date)->whereNotNull('check_in'));
                break;
            case 'checked_out':
                $query->whereHas('attendances', fn ($q) => $q->whereDate('date', $date)->whereNotNull('check_out'));
                break;
            case 'still_inside':
                $query->whereHas('attendances', fn ($q) => $q->whereDate('date', $date)
                    ->whereNotNull('check_in')->whereNull('check_out'));
                break;
        }

        if (Auth::user()->isParent()) {
            $employeeId = Auth::user()->employeeId();

            $employeeId
                ? $query->where('employee_id', $employeeId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function checkIn(Request $request, Child $child)
    {
        Gate::authorize('create', Attendance::class);

        $request->validate(['date' => 'nullable|date_format:Y-m-d|before_or_equal:today']);

        if ($child->status !== 'active') {
            return back()->with('error', 'Inactive children cannot be checked in.');
        }

        $date = $request->input('date', today()->toDateString());

        $result = DB::transaction(function () use ($child, $date) {
            $attendance = Attendance::where('child_id', $child->id)
                ->whereDate('date', $date)->lockForUpdate()->first()
                ?? new Attendance(['child_id' => $child->id, 'date' => $date]);

            if ($attendance->check_in) {
                return false; // already checked in
            }

            $attendance->check_in      = now()->format('H:i:s');
            $attendance->checked_in_by = $this->currentStaffId();
            $attendance->save();

            return true;
        });

        if (!$result) {
            return back()->with('error', "{$child->name} is already checked in for this date.");
        }

        Log::info('Attendance check-in', ['child_id' => $child->id, 'date' => $date, 'by' => Auth::id()]);

        return back()->with('success', "{$child->name} checked in successfully.");
    }

    public function checkOut(Request $request, Child $child)
    {
        Gate::authorize('create', Attendance::class);

        $request->validate(['date' => 'nullable|date_format:Y-m-d|before_or_equal:today']);

        $date = $request->input('date', today()->toDateString());

        $result = DB::transaction(function () use ($child, $date) {
            $attendance = Attendance::where('child_id', $child->id)
                ->whereDate('date', $date)->lockForUpdate()->first();

            if (!$attendance || !$attendance->check_in) {
                return 'not_checked_in';
            }

            if ($attendance->check_out) {
                return 'already_out';
            }

            $attendance->check_out      = now()->format('H:i:s');
            $attendance->checked_out_by = $this->currentStaffId();
            $attendance->save();

            return 'ok';
        });

        if ($result === 'not_checked_in') {
            return back()->with('error', "{$child->name} has not been checked in for this date.");
        }

        if ($result === 'already_out') {
            return back()->with('error', "{$child->name} is already checked out for this date.");
        }

        Log::info('Attendance check-out', ['child_id' => $child->id, 'date' => $date, 'by' => Auth::id()]);

        return back()->with('success', "{$child->name} checked out successfully.");
    }

    /**
     * Safe Y-m-d date: invalid or future dates fall back to today.
     * Public so PdfController can reuse it.
     */
    public function resolveDate(Request $request): string
    {
        $input = $request->input('date');

        if (!$input || !is_string($input)) {
            return today()->toDateString();
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $input);
        } catch (\Exception $e) {
            return today()->toDateString();
        }

        if (!$date || $date->format('Y-m-d') !== $input || $date->gt(today())) {
            return today()->toDateString();
        }

        return $date->toDateString();
    }

    protected function currentStaffId(): ?int
    {
        return Staff::where('user_id', Auth::id())->value('id');
    }
}
