@extends("layouts.app")
@section("title", "Add Staff")
@section("content")
<h3 class="mb-3">Add Staff Member</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("staff.store") }}">
        @csrf
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" maxlength="255" value="{{ old("name") }}" required></div>
        <div class="mb-3"><label class="form-label">Position</label><input type="text" name="position" class="form-control" maxlength="255" value="{{ old("position") }}"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" maxlength="50" value="{{ old("phone") }}"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" maxlength="255" value="{{ old("email") }}"></div>
        <div class="mb-3"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control" max="{{ today()->toDateString() }}" value="{{ old("hire_date") }}"></div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="create_login" value="1" class="form-check-input" id="create_login" {{ old("create_login") ? "checked" : "" }}>
            <label class="form-check-label" for="create_login">Create login account for this staff member (requires the email above)</label>
        </div>
        <div class="mb-3">
            <label class="form-label">Password (required if creating login)</label>
            <input type="password" name="password" class="form-control" minlength="10" maxlength="255"
                   autocomplete="new-password" placeholder="Min 10 characters, letters and numbers">
        </div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route("staff.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
