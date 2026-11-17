@extends("layouts.app")
@section("title", "Edit Child")
@section("content")
    <h3 class="mb-3">Edit Child</h3>
    <div class="card p-4" style="max-width:600px">
        <form method="POST" action="{{ route("children.update", $child) }}" enctype="multipart/form-data">
            @csrf @method("PUT")
            <div class="mb-3">
                <label class="form-label">Parent / Employee</label>
                <select name="employee_id" class="form-select" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected($employee->id === $child->employee_id)>{{ $employee->name }} ({{ $employee->employee_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Child Name</label>
                <input type="text" name="name" class="form-control" value="{{ $child->name }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control"
                       value="{{ $child->date_of_birth->format("Y-m-d") }}" max="{{ today()->toDateString() }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">-</option>
                    <option value="male" @selected($child->gender==="male")>Male</option>
                    <option value="female" @selected($child->gender==="female")>Female</option>
                    <option value="other" @selected($child->gender==="other")>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Photo</label>
                @if($child->photo_url)
                    <div class="mb-2">
                        <img src="{{ $child->photo_url }}" alt="" class="rounded"
                             style="width:100px;height:100px;object-fit:cover;">
                    </div>
                @endif
                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG or WebP, max 2 MB.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" @selected($child->status==="active")>Active</option>
                    <option value="inactive" @selected($child->status==="inactive")>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ $child->notes }}</textarea>
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route("children.index") }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
@endsection
