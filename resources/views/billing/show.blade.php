@extends("layouts.app")
@section("title", "Invoice")
@section("content")
    @php $isOffice = in_array(auth()->user()->role, ["admin", "staff"]); @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Invoice — {{ $invoice->month }} {{ $invoice->year }}</h3>
        <a href="{{ route("billing.index") }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card p-3">
                <p class="mb-1"><strong>Employee:</strong> {{ $invoice->employee->name ?? "-" }}</p>
                <p class="mb-1"><strong>Child:</strong> {{ $invoice->child->name ?? "-" }}</p>
                <p class="mb-1"><strong>Amount:</strong> {{ number_format($invoice->amount, 2) }} tk</p>
                <p class="mb-1"><strong>Paid:</strong> {{ number_format($invoice->paid_amount, 2) }} tk</p>
                <p class="mb-1"><strong>Balance:</strong> {{ number_format($invoice->balance, 2) }} tk</p>
                <p class="mb-1"><strong>Due Date:</strong> {{ optional($invoice->due_date)->format("d M Y") ?? "-" }}</p>
                <p class="mb-0"><strong>Status:</strong>
                    <span class="badge bg-{{ $invoice->status === "paid" ? "success" : "warning" }}">{{ ucfirst($invoice->status) }}</span>
                </p>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card p-3">
                <h6>Payments</h6>
                <table class="table table-sm align-middle">
                    <thead>
                    <tr><th>Date</th><th>Amount</th><th>Method</th><th>Txn ID</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_date->format("d M Y") }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace("_", " ", $payment->method)) }}</td>
                            <td>{{ $payment->reference ?? "-" }}</td>
                            <td>
                            <span class="badge bg-{{ $payment->status === "approved" ? "success" : ($payment->status === "pending" ? "warning" : "danger") }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                            </td>
                            <td class="text-end">
                                @if($isOffice && $payment->status === "pending")
                                    <form action="{{ route("billing.payments.approve", $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route("billing.payments.reject", $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>

                @if($invoice->status !== "paid")
                    @if($isOffice)
                        {{-- Office records a payment directly (approved immediately) --}}
                        <form method="POST" action="{{ route("billing.payments.store", $invoice) }}" class="row g-2 mt-2">
                            @csrf
                            <div class="col-4"><input type="number" step="0.01" name="amount" class="form-control form-control-sm" placeholder="Amount" required></div>
                            <div class="col-4"><input type="date" name="paid_date" class="form-control form-control-sm" value="{{ date("Y-m-d") }}" required></div>
                            <div class="col-4">
                                <select name="method" class="form-select form-select-sm" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-12"><input type="text" name="reference" class="form-control form-control-sm" placeholder="Reference (optional)"></div>
                            <div class="col-12"><button class="btn btn-sm btn-primary">Record Payment</button></div>
                        </form>
                    @else
                        {{-- Parent submits transaction ID, waits for approval --}}
                        <hr>
                        <h6>I have sent the payment</h6>
                        <form method="POST" action="{{ route("billing.submitPayment", $invoice) }}" class="row g-2">
                            @csrf
                            <div class="col-6">
                                <input type="number" step="0.01" name="amount" class="form-control form-control-sm"
                                       value="{{ old("amount", $invoice->balance) }}" placeholder="Amount (tk)" required>
                            </div>
                            <div class="col-6">
                                <input type="date" name="paid_date" class="form-control form-control-sm"
                                       value="{{ old("paid_date", date("Y-m-d")) }}" required>
                            </div>
                            <div class="col-6">
                                <select name="method" class="form-select form-select-sm" required>
                                    <option value="mobile_banking">bKash / Nagad / Rocket</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <input type="text" name="reference" class="form-control form-control-sm"
                                       value="{{ old("reference") }}" placeholder="Transaction ID" required>
                            </div>
                            <div class="col-12"><button class="btn btn-sm btn-primary">Submit Payment</button></div>
                        </form>
                        <div class="small text-muted mt-2">The office will confirm your payment after checking the transaction ID.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection