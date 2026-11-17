<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    // Parents may open the attendance page; the controller scopes it to their own children.
    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isParent();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isManager()) {
            return true;
        }

        $id = $user->employeeId();

        return $user->isParent() && $id !== null
            && (int) $attendance->child?->employee_id === $id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin();
    }
}
