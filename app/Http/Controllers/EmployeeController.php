<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Support\Sanitize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Employee::class);

        $request->validate(['search' => 'nullable|string|max:100']);

        $employees = Employee::when($request->filled('search'), function ($q) use ($request) {
                $s = Sanitize::like($request->input('search'));

                $q->where(function ($q) use ($s) {
                    $q->where("name", "like", "%{$s}%")
                      ->orWhere("employee_code", "like", "%{$s}%");
                });
            })
            ->withCount("children")
            ->orderBy("name")
            ->paginate(15)
            ->withQueryString();

        return view("employees.index", compact("employees"));
    }

    public function create()
    {
        Gate::authorize('create', Employee::class);

        return view("employees.create");
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Employee::class);

        $validated = $request->validate([
            "name"          => "required|string|max:255",
            "employee_code" => "nullable|string|max:50|unique:employees,employee_code",
            "department"    => "nullable|string|max:255",
            "designation"   => "nullable|string|max:255",
            "phone"         => "nullable|string|max:50",
            "email"         => "nullable|email|max:255",
            "address"       => "nullable|string|max:255",
            "create_login"  => "nullable|boolean",
            // No default password: one must be supplied when a login is created.
            "password"      => ["nullable", "required_if:create_login,1", Password::min(10)->letters()->numbers()],
        ]);

        if ($request->boolean("create_login")) {
            $request->validate([
                "email" => "required|email|max:255|unique:users,email",
            ], [
                "email.required" => "Email is required to create a login account.",
                "email.unique"   => "This email already has a login account.",
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $userId = null;

            // Login account (role is always "parent" for employees)
            if ($request->boolean("create_login")) {
                $user = new User([
                    "name"     => $validated["name"],
                    "email"    => Str::lower($validated["email"]),
                    "password" => $validated["password"], // hashed by model cast
                ]);
                $user->role = "parent";
                $user->save();

                $userId = $user->id;
            }

            unset($validated["create_login"], $validated["password"]);

            $employee = new Employee($validated);
            $employee->user_id = $userId; // not mass-assignable
            $employee->save();
        });

        Log::info('Employee created', ['by' => Auth::id()]);

        return redirect()->route("employees.index")->with("success", "Employee added successfully.");
    }

    public function edit(Employee $employee)
    {
        Gate::authorize('update', $employee);

        return view("employees.edit", compact("employee"));
    }

    public function update(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            "name"          => "required|string|max:255",
            "employee_code" => "nullable|string|max:50|unique:employees,employee_code," . $employee->id,
            "department"    => "nullable|string|max:255",
            "designation"   => "nullable|string|max:255",
            "phone"         => "nullable|string|max:50",
            "email"         => "nullable|email|max:255",
            "address"       => "nullable|string|max:255",
        ]);

        $employee->update($validated);

        return redirect()->route("employees.index")->with("success", "Employee updated successfully.");
    }

    public function destroy(Employee $employee)
    {
        Gate::authorize('delete', $employee);

        if ($employee->children()->exists() || $employee->invoices()->exists()) {
            return back()->with("error", "Cannot delete: this employee still has children or invoices.");
        }

        DB::transaction(function () use ($employee) {
            $userId = $employee->user_id;

            $employee->delete();

            // Don't leave an orphaned, still-active login behind.
            if ($userId) {
                User::whereKey($userId)->where('role', 'parent')->delete();
            }
        });

        Log::info('Employee deleted', ['employee_id' => $employee->id, 'by' => Auth::id()]);

        return redirect()->route("employees.index")->with("success", "Employee removed.");
    }
}
