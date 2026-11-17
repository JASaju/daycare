<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmergencyContactController extends Controller
{
    public function store(Request $request, Child $child)
    {
        Gate::authorize('update', $child);

        $validated = $request->validate([
            "name"            => "required|string|max:255",
            "relation"        => "required|string|max:100",
            "phone"           => ["required", "string", "max:30", "regex:/^[0-9+\-\s()]{5,30}$/"],
            "alternate_phone" => ["nullable", "string", "max:30", "regex:/^[0-9+\-\s()]{5,30}$/"],
        ]);

        $child->emergencyContacts()->create($validated);

        return back()->with("success", "Emergency contact added.");
    }

    public function destroy(Child $child, EmergencyContact $emergencyContact)
    {
        // Contact must belong to this child (defence in depth besides scopeBindings).
        abort_unless((int) $emergencyContact->child_id === (int) $child->id, 404);

        Gate::authorize('delete', $emergencyContact);

        $emergencyContact->delete();

        return back()->with("success", "Emergency contact removed.");
    }
}
