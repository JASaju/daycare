@extends("layouts.app")
@section("title", "Edit Employee")
@section("content")
<h3 class="mb-3">Edit Employee</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("employees.update", $employee) }}">
        @csrf @method("PUT")
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $employee->name }}" required></div>
        <div class="mb-3"><label class="form-label">Employee Code</label><input type="text" name="employee_code" class="form-control" value="{{ $employee->employee_code }}"></div>
        <div class="mb-3"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="{{ $employee->department }}"></div>
        <div class="mb-3"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control" value="{{ $employee->designation }}"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ $employee->phone }}"></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $employee->email }}"></div>
        <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="{{ $employee->address }}"></div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route("employees.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
