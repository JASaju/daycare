<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Staff::class);

        $staff = Staff::orderBy("name")->paginate(15);

        return view("staff.index", compact("staff"));
    }

    public function create()
    {
        Gate::authorize('create', Staff::class);

        return view("staff.create");
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Staff::class);

        $validated = $request->validate([
            "name"         => "required|string|max:255",
            "position"     => "nullable|string|max:255",
            "phone"        => "nullable|string|max:50",
            "email"        => ["nullable", "email", "max:255", "required_if:create_login,1"],
            "hire_date"    => "nullable|date|before_or_equal:today",
            "create_login" => "nullable|boolean",
            // No default password: must be supplied when a login is created.
            "password"     => ["nullable", "required_if:create_login,1", Password::min(10)->letters()->numbers()],
        ]);

        if ($request->boolean("create_login")) {
            $request->validate(["email" => "unique:users,email"], [
                "email.unique" => "This email already has a login account.",
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $userId = null;

            if ($request->boolean("create_login")) {
                $user = new User([
                    "name"     => $validated["name"],
                    "email"    => Str::lower($validated["email"]),
                    "password" => $validated["password"], // hashed by model cast
                ]);
                $user->role = "staff";
                $user->save();

                $userId = $user->id;
            }

            $staff = new Staff([
                "name"      => $validated["name"],
                "position"  => $validated["position"] ?? null,
                "phone"     => $validated["phone"] ?? null,
                "email"     => $validated["email"] ?? null,
                "hire_date" => $validated["hire_date"] ?? null,
                "status"    => "active",
            ]);
            $staff->user_id = $userId; // not mass-assignable
            $staff->save();
        });

        Log::info('Staff created', ['by' => Auth::id()]);

        return redirect()->route("staff.index")->with("success", "Staff member added.");
    }

    public function edit(Staff $staffMember)
    {
        Gate::authorize('update', $staffMember);

        return view("staff.edit", ["staff" => $staffMember]);
    }

    public function update(Request $request, Staff $staffMember)
    {
        Gate::authorize('update', $staffMember);

        $validated = $request->validate([
            "name"      => "required|string|max:255",
            "position"  => "nullable|string|max:255",
            "phone"     => "nullable|string|max:50",
            "email"     => "nullable|email|max:255",
            "hire_date" => "nullable|date|before_or_equal:today",
            "status"    => "required|in:active,inactive",
        ]);

        $staffMember->update($validated);

        return redirect()->route("staff.index")->with("success", "Staff record updated.");
    }

    public function destroy(Staff $staffMember)
    {
        Gate::authorize('delete', $staffMember);

        DB::transaction(function () use ($staffMember) {
            $userId = $staffMember->user_id;

            $staffMember->delete();

            // Remove the login too, but never an admin account.
            if ($userId) {
                User::whereKey($userId)->where('role', 'staff')->delete();
            }
        });

        Log::info('Staff deleted', ['staff_id' => $staffMember->id, 'by' => Auth::id()]);

        return redirect()->route("staff.index")->with("success", "Staff member removed.");
    }
}
