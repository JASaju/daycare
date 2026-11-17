<?php

namespace App\Policies;

use App\Models\EmergencyContact;
use App\Models\User;

class EmergencyContactPolicy
{
    public function view(User $user, EmergencyContact $contact): bool
    {
        if ($user->isManager()) {
            return true;
        }

        $id = $user->employeeId();

        return $user->isParent() && $id !== null
            && (int) $contact->child?->employee_id === $id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, EmergencyContact $contact): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, EmergencyContact $contact): bool
    {
        return $user->isManager();
    }
}
