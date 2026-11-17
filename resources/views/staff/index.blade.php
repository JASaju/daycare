@extends("layouts.app")
@section("title", "Staff")
@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Staff / Caregivers</h3>
    <a href="{{ route("staff.create") }}" class="btn btn-primary btn-sm">+ Add Staff</a>
</div>
<div class="card">
    <table class="table mb-0">
        <thead><tr><th>#</th><th>Name</th><th>Position</th><th>Phone</th><th>Hire Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($staff as $member)
            <tr>
                <td>{{ $staff->firstItem() + $loop->index }}</td>
                <td>{{ $member->name }}</td>
                <td>{{ $member->position }}</td>
                <td>{{ $member->phone }}</td>
                <td>{{ optional($member->hire_date)->format("d M Y") }}</td>
                <td><span class="badge bg-{{ $member->status === "active" ? "success" : "secondary" }}">{{ ucfirst($member->status) }}</span></td>
                <td class="text-end">
                    <a href="{{ route("staff.edit", $member) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route("staff.destroy", $member) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Remove this staff member and their login account?')">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No staff found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $staff->links() }}</div>
@endsection
