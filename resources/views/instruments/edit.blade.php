@extends("layouts.app")
@section("title", "Edit Item")
@section("content")
<h3 class="mb-3">Edit Inventory Item</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("instruments.update", $instrument) }}">
        @csrf @method("PUT")
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ $instrument->name }}" required></div>
        <div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{ $instrument->category }}"></div>
        <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" value="{{ $instrument->quantity }}" required></div>
        <div class="mb-3">
            <label class="form-label">Condition</label>
            <select name="condition" class="form-select" required>
                <option value="new" @selected($instrument->condition==="new")>New</option>
                <option value="good" @selected($instrument->condition==="good")>Good</option>
                <option value="fair" @selected($instrument->condition==="fair")>Fair</option>
                <option value="damaged" @selected($instrument->condition==="damaged")>Damaged</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ optional($instrument->purchase_date)->format("Y-m-d") }}"></div>
        <div class="mb-3"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ $instrument->purchase_price }}"></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ $instrument->notes }}</textarea></div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route("instruments.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
