@extends("layouts.app")
@section("title", "Enroll Child")
@section("content")
    <h3 class="mb-3">Enroll Child</h3>
    <div class="card p-4" style="max-width:600px">
        <form method="POST" action="{{ route("children.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Parent / Employee</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">Select employee...</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Child Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" max="{{ today()->toDateString() }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">-</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG or WebP, max 2 MB.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route("children.index") }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
@endsection
