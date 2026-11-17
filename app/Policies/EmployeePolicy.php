<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->isManager()) {
            return true;
        }

        return $user->isParent() && $user->employeeId() === (int) $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }
}
