@extends("layouts.app")
@section("title", "Employees")
@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Employees</h3>
    <a href="{{ route("employees.create") }}" class="btn btn-primary btn-sm">+ Add Employee</a>
</div>
<form class="mb-3">
    <input type="text" name="search" class="form-control" style="max-width:300px" placeholder="Search..." value="{{ request("search") }}">
</form>
<div class="card">
    <table class="table mb-0">
        <thead><tr>
            <th>#</th>
            <th>Name</th>
            <th>Code</th>
            <th>Department</th>
            <th>Phone</th>
            <th># of Children</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($employees as $employee)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->employee_code }}</td>
                <td>{{ $employee->department }}</td>
                <td>{{ $employee->phone }}</td>
                <td>{{ $employee->children_count }}</td>
                <td class="text-end">
                    <a href="{{ route("employees.edit", $employee) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route("employees.destroy", $employee) }}" method="POST" class="d-inline" onsubmit="return confirm(\"Delete?\")">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No employees found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $employees->links() }}</div>
@endsection
