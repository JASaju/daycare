<?php

namespace App\Policies;

use App\Models\HealthRecord;
use App\Models\User;

class HealthRecordPolicy
{
    public function view(User $user, HealthRecord $record): bool
    {
        if ($user->isManager()) {
            return true;
        }

        $id = $user->employeeId();

        return $user->isParent() && $id !== null
            && (int) $record->child?->employee_id === $id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, HealthRecord $record): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, HealthRecord $record): bool
    {
        return $user->isAdmin();
    }
}
