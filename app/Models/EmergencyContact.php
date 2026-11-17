<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    // "child_id" is set from the route's $child in the controller
    // ($child->emergencyContacts()->create($validated)), never from user input.
    protected $fillable = ["name", "relation", "phone", "alternate_phone"];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
