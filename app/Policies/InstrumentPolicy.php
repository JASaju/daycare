<?php

namespace App\Policies;

use App\Models\Instrument;
use App\Models\User;

class InstrumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function view(User $user, Instrument $instrument): bool
    {
        return $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Instrument $instrument): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Instrument $instrument): bool
    {
        return $user->isAdmin();
    }
}
