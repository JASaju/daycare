@extends("layouts.app")
@section("title", "Edit Staff")
@section("content")
<h3 class="mb-3">Edit Staff Member</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("staff.update", $staff) }}">
        @csrf @method("PUT")
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" maxlength="255" value="{{ old("name", $staff->name) }}" required></div>
        <div class="mb-3"><label class="form-label">Position</label><input type="text" name="position" class="form-control" maxlength="255" value="{{ old("position", $staff->position) }}"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" maxlength="50" value="{{ old("phone", $staff->phone) }}"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" maxlength="255" value="{{ old("email", $staff->email) }}"></div>
        <div class="mb-3"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control" max="{{ today()->toDateString() }}" value="{{ old("hire_date", optional($staff->hire_date)->format("Y-m-d")) }}"></div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" @selected(old("status", $staff->status) === "active")>Active</option>
                <option value="inactive" @selected(old("status", $staff->status) === "inactive")>Inactive</option>
            </select>
            <div class="form-text">Inactive staff can no longer log in.</div>
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route("staff.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
