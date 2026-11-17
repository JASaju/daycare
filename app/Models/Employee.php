<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    // SECURITY: "user_id" is NOT mass-assignable, so a request can't link an
    // employee to someone else's login. Set it explicitly in the controller:
    //   $employee = new Employee($validated); $employee->user_id = $id; $employee->save();
    protected $fillable = [
        "name", "employee_code", "department",
        "designation", "phone", "email", "address",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
