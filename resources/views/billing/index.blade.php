@extends("layouts.app")
@section("title", "Billing")
@section("content")

    {{-- Payment instructions card (visible to everyone) --}}
    <div class="card border-success mb-4">
        <div class="card-header bg-success text-white fw-semibold">
            How to Send Payment
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-semibold mb-1">Merchant Payment</div>
                        <div class="fs-5 fw-bold">
                            <span id="merchant-no">{{ config("payment.merchant_number") }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                    onclick="copyText('merchant-no', this)">Copy</button>
                        </div>
                        <div class="small text-muted">bKash / Nagad Merchant</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-semibold mb-1">Mobile Banking (Personal)</div>
                        <div class="fs-5 fw-bold">
                            <span id="mobile-no">{{ config("payment.mobile_number") }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                    onclick="copyText('mobile-no', this)">Copy</button>
                        </div>
                        <div class="small text-muted">bKash / Nagad / Rocket (Send Money)</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-semibold mb-1">Bank Account</div>
                        <div class="fw-bold">{{ config("payment.bank_name") }}</div>
                        <div>A/C: <span id="bank-no" class="fw-bold">{{ config("payment.bank_account") }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                    onclick="copyText('bank-no', this)">Copy</button>
                        </div>
                        <div class="small text-muted">{{ config("payment.bank_account_name") }}</div>
                    </div>
                </div>

            </div>

            <div class="alert alert-warning mt-3 mb-0 py-2 small">
                After sending money, use your <strong>child's name and invoice month</strong> as the reference,
                then inform the office with the transaction ID.
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Billing &amp; Invoices</h3>
        @if(auth()->user()->role === "admin")
            <a href="{{ route("billing.create") }}" class="btn btn-primary btn-sm">+ New Invoice</a>
        @endif
    </div>

    <div class="card">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Child</th>
                <th>Period</th>
                <th>Amount</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $invoice->employee->name ?? "-" }}</td>
                    <td>{{ $invoice->child->name ?? "-" }}</td>
                    <td>{{ $invoice->month }} {{ $invoice->year }}</td>
                    <td>{{ number_format($invoice->amount, 2) }} tk</td>
                    <td>
                        <span class="badge bg-{{ $invoice->status === "paid" ? "success" : ($invoice->status === "partial" ? "info" : "warning") }}">{{ ucfirst($invoice->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route("billing.show", $invoice) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        @if(auth()->user()->role === "admin")
                            <form action="{{ route("billing.destroy", $invoice) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this invoice?')">
                                @csrf @method("DELETE")
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No invoices found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>

    <script>
        function copyText(id, btn) {
            const text = document.getElementById(id).innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const old = btn.innerText;
            btn.innerText = "Copied";
            setTimeout(() => btn.innerText = old, 1500);
        });
        }
    </script>
@endsection