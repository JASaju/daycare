@extends("layouts.app")
@section("title", "Health Record")
@section("content")
<h3 class="mb-3">Health Record — {{ $child->name }}</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("health.update", $child) }}">
        @csrf @method("PUT")
        <div class="mb-3">
            <label class="form-label">Blood Group</label>
            <input type="text" name="blood_group" class="form-control" value="{{ $record->blood_group ?? "" }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Allergies</label>
            <textarea name="allergies" class="form-control" rows="2">{{ $record->allergies ?? "" }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Medications</label>
            <textarea name="medications" class="form-control" rows="2">{{ $record->medications ?? "" }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Medical Conditions</label>
            <textarea name="medical_conditions" class="form-control" rows="2">{{ $record->medical_conditions ?? "" }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Doctor Name</label>
            <input type="text" name="doctor_name" class="form-control" value="{{ $record->doctor_name ?? "" }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Doctor Phone</label>
            <input type="text" name="doctor_phone" class="form-control" value="{{ $record->doctor_phone ?? "" }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Immunization Notes</label>
            <textarea name="immunization_notes" class="form-control" rows="2">{{ $record->immunization_notes ?? "" }}</textarea>
        </div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route("children.show", $child) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
