<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager() || $user->isParent();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isManager()) {
            return true;
        }

        $id = $user->employeeId();

        return $user->isParent() && $id !== null && (int) $invoice->employee_id === $id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }
}
