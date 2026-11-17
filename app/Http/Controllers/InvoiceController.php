<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    private const METHODS = 'cash,bank_transfer,card,mobile_banking';

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $request->validate(['status' => 'nullable|in:unpaid,partial,paid,overdue']);

        $query = Invoice::with(["employee", "child", "payments"]);

        if (Auth::user()->isParent()) {
            $employeeId = Auth::user()->employeeId();

            $employeeId
                ? $query->where("employee_id", $employeeId)
                : $query->whereRaw('1 = 0');
        }

        if ($request->filled('status')) {
            $query->where("status", $request->input('status'));
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view("billing.index", compact("invoices"));
    }

    public function create()
    {
        Gate::authorize('create', Invoice::class);

        $employees = Employee::with("children")->orderBy("name")->get();

        return view("billing.create", compact("employees"));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validate([
            "employee_id" => "required|integer|exists:employees,id",
            // the child must belong to the chosen employee
            "child_id"    => ["nullable", "integer",
                Rule::exists('children', 'id')->where('employee_id', $request->input('employee_id'))],
            "month"       => "required|in:January,February,March,April,May,June,July,August,September,October,November,December",
            "year"        => "required|integer|min:2000|max:2100",
            "amount"      => "required|numeric|min:0.01|max:99999999",
            "due_date"    => "nullable|date",
            "notes"       => "nullable|string|max:1000",
        ]);

        $validated['status'] = 'unpaid';

        $invoice = Invoice::create($validated);

        Log::info('Invoice created', ['invoice_id' => $invoice->id, 'by' => Auth::id()]);

        return redirect()->route("billing.index")->with("success", "Invoice created.");
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load(["employee", "child", "payments"]);

        return view("billing.show", compact("invoice"));
    }

    /**
     * Staff/admin records a payment (approved immediately).
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        Gate::authorize('create', Payment::class);

        $validated = $request->validate([
            "amount"    => "required|numeric|min:0.01|max:99999999",
            "paid_date" => "required|date|before_or_equal:today",
            "method"    => "required|in:" . self::METHODS,
            "reference" => "nullable|string|max:100|unique:payments,reference",
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $this->assertWithinBalance($locked, (float) $validated['amount']);

            $locked->payments()->create($validated + ['status' => 'approved']);

            $this->syncStatus($locked);
        });

        Log::info('Payment recorded', ['invoice_id' => $invoice->id, 'amount' => $validated['amount'], 'by' => Auth::id()]);

        return back()->with("success", "Payment recorded.");
    }

    /**
     * Parent submits a transaction ID; it stays "pending" until approved.
     */
    public function submitPayment(Request $request, Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        if ($invoice->status === 'paid') {
            return back()->with("error", "This invoice is already paid.");
        }

        $validated = $request->validate([
            "amount"    => "required|numeric|min:0.01|max:99999999",
            "method"    => "required|in:" . self::METHODS,
            "reference" => ["required", "string", "max:100", "regex:/^[A-Za-z0-9._\-\/ ]+$/", "unique:payments,reference"],
            "paid_date" => "nullable|date|before_or_equal:today",
        ]);

        // Stop flooding the approval queue.
        if ($invoice->payments()->where('status', 'pending')->count() >= 3) {
            return back()->with("error", "You already have pending payments for this invoice. Please wait for approval.");
        }

        DB::transaction(function () use ($invoice, $validated) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $this->assertWithinBalance($locked, (float) $validated['amount']);

            $locked->payments()->create([
                "amount"    => $validated['amount'],
                "method"    => $validated['method'],
                "reference" => $validated['reference'],
                "paid_date" => $validated['paid_date'] ?? today()->toDateString(),
                "status"    => 'pending',
            ]);
        });

        Log::info('Payment submitted', ['invoice_id' => $invoice->id, 'by' => Auth::id()]);

        return back()->with("success", "Payment submitted for approval.");
    }

    public function approvePayment(Payment $payment)
    {
        Gate::authorize('approve', $payment);

        $outcome = DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return 'not_pending';
            }

            $invoice = Invoice::whereKey($locked->invoice_id)->lockForUpdate()->firstOrFail();

            if ((float) $locked->amount > $this->approvedBalance($invoice) + 0.001) {
                return 'exceeds';
            }

            $locked->status = 'approved';
            $locked->save();

            $this->syncStatus($invoice);

            return 'ok';
        });

        if ($outcome === 'not_pending') {
            return back()->with("error", "This payment has already been processed.");
        }

        if ($outcome === 'exceeds') {
            return back()->with("error", "Approving this payment would exceed the invoice balance.");
        }

        Log::info('Payment approved', ['payment_id' => $payment->id, 'by' => Auth::id()]);

        return back()->with("success", "Payment approved.");
    }

    public function rejectPayment(Payment $payment)
    {
        Gate::authorize('approve', $payment);

        $changed = DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return false;
            }

            $locked->status = 'rejected';
            $locked->save();

            return true;
        });

        if (!$changed) {
            return back()->with("error", "This payment has already been processed.");
        }

        Log::info('Payment rejected', ['payment_id' => $payment->id, 'by' => Auth::id()]);

        return back()->with("success", "Payment rejected.");
    }

    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        // Never delete financial history.
        if ($invoice->payments()->exists()) {
            return back()->with("error", "Cannot delete an invoice that has payments.");
        }

        $invoice->delete();

        Log::info('Invoice deleted', ['invoice_id' => $invoice->id, 'by' => Auth::id()]);

        return redirect()->route("billing.index")->with("success", "Invoice deleted.");
    }

    /* ------------------------------------------------------------------ */

    protected function approvedBalance(Invoice $invoice): float
    {
        $paid = (float) $invoice->payments()->where('status', 'approved')->sum('amount');

        return round((float) $invoice->amount - $paid, 2);
    }

    protected function assertWithinBalance(Invoice $invoice, float $amount): void
    {
        $balance = $this->approvedBalance($invoice);

        if ($amount > $balance + 0.001) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds the outstanding balance (' . number_format($balance, 2) . ').',
            ]);
        }
    }

    protected function syncStatus(Invoice $invoice): void
    {
        $paid = (float) $invoice->payments()->where('status', 'approved')->sum('amount');

        $invoice->status = $paid + 0.001 >= (float) $invoice->amount
            ? 'paid'
            : ($paid > 0 ? 'partial' : 'unpaid');

        $invoice->save();
    }
}
