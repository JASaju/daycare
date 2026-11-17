@extends("layouts.app")
@section("title", "New Invoice")
@section("content")
<h3 class="mb-3">Create Invoice</h3>
<div class="card p-4" style="max-width:600px">
    <form method="POST" action="{{ route("billing.store") }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" id="employee_id" class="form-select" required onchange="updateChildren()">
                <option value="">Select employee...</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" data-children="{{ $employee->children->pluck("name","id") }}">{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Child (optional)</label>
            <select name="child_id" id="child_id" class="form-select">
                <option value="">-</option>
            </select>
        </div>
        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label">Month</label>
                <select name="month" class="form-control" required>
                    <option value="">Select Month</option>
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>
                </select>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="{{ date("Y") }}" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        <button class="btn btn-primary">Create Invoice</button>
        <a href="{{ route("billing.index") }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
<script>
const employees = @json($employees->keyBy("id"));
function updateChildren() {
    const empId = document.getElementById("employee_id").value;
    const childSelect = document.getElementById("child_id");
    childSelect.innerHTML = "<option value=\"\">-</option>";
    if (empId && employees[empId]) {
        employees[empId].children.forEach(c => {
            const opt = document.createElement("option");
            opt.value = c.id;
            opt.textContent = c.name;
            childSelect.appendChild(opt);
        });
    }
}
</script>
@endsection
