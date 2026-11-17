<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // SECURITY: "role" is NOT mass-assignable (prevents privilege escalation).
    // Set it explicitly:  $user->role = 'parent'; $user->save();
    protected $fillable = [
        "name",
        "email",
        "password",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === "admin";
    }

    public function isStaff(): bool
    {
        return $this->role === "staff";
    }

    public function isParent(): bool
    {
        return $this->role === "parent";
    }

    /** admin or staff */
    public function isManager(): bool
    {
        return $this->isAdmin() || $this->isStaff();
    }

    /** The employee (parent) record id linked to this login, if any. */
    public function employeeId(): ?int
    {
        return $this->employee?->id;
    }
}
