<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    // "child_id" is set from the route's $child in the controller
    // (updateOrCreate(['child_id' => $child->id], $validated)), never from user input.
    protected $fillable = [
        "blood_group", "allergies", "medications",
        "medical_conditions", "doctor_name", "doctor_phone", "immunization_notes",
    ];

    // OPTIONAL (recommended for medical data): encrypt at rest.
    // Requires the columns to be TEXT and existing rows to be re-saved first,
    // otherwise old plaintext rows will throw a decryption error. Uncomment when ready.
    //
    // protected $casts = [
    //     "allergies"          => "encrypted",
    //     "medications"        => "encrypted",
    //     "medical_conditions" => "encrypted",
    //     "doctor_phone"       => "encrypted",
    //     "immunization_notes" => "encrypted",
    // ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
