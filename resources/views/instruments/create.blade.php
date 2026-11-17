@extends("layouts.app")
@section("title", "Add Item")
@section("content")
<h3 class="mb-3">Add Inventory Item</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("instruments.store") }}">
        @csrf
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="Toy, Furniture, Learning Tool, Safety Equipment..."></div>
        <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" value="1" required></div>
        <div class="mb-3">
            <label class="form-label">Condition</label>
            <select name="condition" class="form-select" required>
                <option value="new">New</option>
                <option value="good" selected>Good</option>
                <option value="fair">Fair</option>
                <option value="damaged">Damaged</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route("instruments.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
