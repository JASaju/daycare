<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = "staff";

    // SECURITY: "user_id" is NOT mass-assignable. Set it explicitly in the controller.
    protected $fillable = ["name", "position", "phone", "email", "hire_date", "status"];

    protected $casts = [
        "hire_date" => "date",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
