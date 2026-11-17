<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isParent();
    }

    public function view(User $user, Child $child): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        if ($user->isParent()) {
            $employeeId = $user->employee?->id;

            return $employeeId !== null && (int) $child->employee_id === (int) $employeeId;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, Child $child): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function delete(User $user, Child $child): bool
    {
        return $user->isAdmin();
    }
}
