@extends("layouts.app")
@section("title", "Add Employee")
@section("content")
    <h3 class="mb-3">Add Employee</h3>
    <div class="card p-4" style="max-width:600px">
        <form method="POST" action="{{ route("employees.store") }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" value="{{ old("name") }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Employee Code</label><input type="text" name="employee_code" value="{{ old("employee_code") }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Department</label><input type="text" name="department" value="{{ old("department") }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Designation</label><input type="text" name="designation" value="{{ old("designation") }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" value="{{ old("phone") }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old("email") }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" value="{{ old("address") }}" class="form-control"></div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="create_login" value="1" class="form-check-input" id="create_login" {{ old("create_login") ? "checked" : "" }}>
                <label class="form-check-label" for="create_login">Create login account for this employee (uses email above)</label>
            </div>
            <div class="mb-3"><label class="form-label">Password (if creating login)</label><input type="password" name="password" class="form-control" placeholder="Default: password"></div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route("employees.index") }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
@endsection