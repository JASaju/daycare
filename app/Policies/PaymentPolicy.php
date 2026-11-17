<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isParent();
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isManager()) {
            return true;
        }

        $id = $user->employeeId();

        return $user->isParent() && $id !== null
            && (int) $payment->invoice?->employee_id === $id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /** Approve / reject a pending payment. */
    public function approve(User $user, Payment $payment): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
