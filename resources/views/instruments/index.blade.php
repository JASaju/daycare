@extends("layouts.app")
@section("title", "Instruments & Toys")
@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Instruments, Toys &amp; Equipment</h3>
    <a href="{{ route("instruments.create") }}" class="btn btn-primary btn-sm">+ Add Item</a>
</div>
<form class="mb-3">
    <input type="text" name="search" class="form-control" style="max-width:300px" placeholder="Search..." value="{{ request("search") }}">
</form>
<div class="card">
    <table class="table mb-0">
        <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Qty</th><th>Condition</th><th>Purchased</th><th></th></tr></thead>
        <tbody>
        @forelse($instruments as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category }}</td>
                <td>{{ $item->quantity }}</td>
                <td><span class="badge bg-{{ $item->condition === "damaged" ? "danger" : ($item->condition === "new" ? "success" : "secondary") }}">{{ ucfirst($item->condition) }}</span></td>
                <td>{{ optional($item->purchase_date)->format("d M Y") }}</td>
                <td class="text-end">
                    <a href="{{ route("instruments.edit", $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route("instruments.destroy", $item) }}" method="POST" class="d-inline" onsubmit="return confirm(\"Delete?\")">
                        @csrf @method("DELETE")
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No items in inventory.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $instruments->links() }}</div>
@endsection
