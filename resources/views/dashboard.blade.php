@extends("layouts.app")
@section("title", "Dashboard")
@section("content")
<h3 class="mb-4">Dashboard</h3>
@if(auth()->user()->role !== "parent")
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted small">Active Children</div>
                <div class="fs-3 fw-bold">{{ $childrenCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted small">Active Staff</div>
                <div class="fs-3 fw-bold">{{ $staffCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted small">Checked In Today</div>
                <div class="fs-3 fw-bold">{{ $todayAttendance }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted small">Outstanding Balance</div>
                <div class="fs-3 fw-bold">{{ number_format($outstandingAmount, 2) }}</div>
                <div class="text-muted small">{{ $unpaidInvoices }} unpaid invoice(s)</div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route("children.create") }}" class="btn btn-primary btn-sm">+ Enroll Child</a>
        <a href="{{ route("attendance.index") }}" class="btn btn-outline-secondary btn-sm">Take Attendance</a>
        <a href="{{ route("billing.index") }}" class="btn btn-outline-secondary btn-sm">View Billing</a>
    </div>
@else
    <div class="card p-3 mb-4">
        <h6>Your Children</h6>
        @forelse($children as $child)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong>{{ $child->name }}</strong> — {{ $child->age }} yrs
                </div>
                <div>
                    @php($today = $child->attendances->first())
                    @if($today && $today->check_in)
                        <span class="badge bg-success">Checked in {{ \Carbon\Carbon::parse($today->check_in)->format("g:i A") }}</span>
                    @else
                        <span class="badge bg-secondary">Not checked in today</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No children linked to your account yet. Contact the admin.</p>
        @endforelse
    </div>

    <div class="card p-3">
        <h6>Recent Invoices</h6>
        @forelse($invoices as $invoice)
            <div class="d-flex justify-content-between border-bottom py-2">
                <div>{{ $invoice->month }} {{ $invoice->year }} — {{ number_format($invoice->amount, 2) }}</div>
                <span class="badge bg-{{ $invoice->status === "paid" ? "success" : "warning" }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        @empty
            <p class="text-muted mb-0">No invoices yet.</p>
        @endforelse
    </div>
@endif
@endsection
