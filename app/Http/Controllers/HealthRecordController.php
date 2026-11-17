<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class HealthRecordController extends Controller
{
    public function edit(Child $child)
    {
        Gate::authorize('update', $child);

        $record = $child->healthRecord;

        return view("health.edit", compact("child", "record"));
    }

    public function update(Request $request, Child $child)
    {
        Gate::authorize('update', $child);

        $validated = $request->validate([
            "blood_group"        => "nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-",
            "allergies"          => "nullable|string|max:2000",
            "medications"        => "nullable|string|max:2000",
            "medical_conditions" => "nullable|string|max:2000",
            "doctor_name"        => "nullable|string|max:255",
            "doctor_phone"       => ["nullable", "string", "max:30", "regex:/^[0-9+\-\s()]{5,30}$/"],
            "immunization_notes" => "nullable|string|max:2000",
        ]);

        // child_id is set by the relation, not by input.
        $child->healthRecord()->updateOrCreate([], $validated);

        Log::info('Health record updated', ['child_id' => $child->id, 'by' => Auth::id()]);

        return redirect()->route("children.show", $child)->with("success", "Health record updated.");
    }
}
