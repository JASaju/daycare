<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Employee;
use App\Models\Instrument;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Staff;
use App\Support\Sanitize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class ReportController extends Controller
{
    public function __construct()
    {
        // Defence in depth: routes already use role middleware, but never rely on that alone.
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check() && Auth::user()->isManager(), 403);

            return $next($request);
        });
    }

    public function index()
    {
        $employees = Employee::orderBy('name')->get();

        $categories = Instrument::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('reports.index', compact('employees', 'categories'));
    }

    public function employees(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $this->validateFilters($request);

        $query = Employee::withCount('children');

        if ($request->filled('search')) {
            $search = Sanitize::like($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('employee_code', 'like', '%' . $search . '%');
            });
        }

        $employees = $query->orderBy('name')->get();

        $filters = ['Search' => $request->input('search')];

        return $this->render('pdf.employees', compact('employees', 'filters'), 'employees-report');
    }

    public function staff(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $this->validateFilters($request);

        $query = Staff::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $staff = $query->orderBy('name')->get();

        $filters = ['Status' => $request->input('status')];

        return $this->render('pdf.staff', compact('staff', 'filters'), 'staff-report');
    }

    public function children(Request $request)
    {
        $this->validateFilters($request);

        $query = Child::with('employee');

        $this->limitToParent($query);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . Sanitize::like($request->input('search')) . '%');
        }

        $children = $query->orderBy('name')->get();

        $filters = [
            'Status'          => $request->input('status'),
            'Gender'          => $request->input('gender'),
            'Parent/Employee' => $this->employeeName($request->input('employee_id')),
            'Search'          => $request->input('search'),
        ];

        return $this->render('pdf.children', compact('children', 'filters'), 'children-report');
    }

    public function invoices(Request $request)
    {
        $this->validateFilters($request);

        $query = Invoice::with(['employee', 'child', 'payments']);

        $this->limitToParent($query);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        $invoices = $query->orderBy('year', 'desc')->orderBy('id', 'desc')->get();

        $filters = [
            'Status'          => $request->input('status'),
            'Year'            => $request->input('year'),
            'Month'           => $request->input('month'),
            'Parent/Employee' => $this->employeeName($request->input('employee_id')),
        ];

        return $this->render('pdf.invoices', compact('invoices', 'filters'), 'invoices-report', 'L');
    }

    public function dues(Request $request)
    {
        $this->validateFilters($request);

        $query = Invoice::with(['employee', 'child', 'payments'])
            ->whereIn('status', ['unpaid', 'partial', 'overdue']);

        $this->limitToParent($query);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        $invoices = $query->orderBy('due_date')->get();

        $byEmployee = [];

        foreach ($invoices as $invoice) {
            $id = $invoice->employee_id;

            if (!isset($byEmployee[$id])) {
                $byEmployee[$id] = [
                    'name'    => $invoice->employee ? $invoice->employee->name : '-',
                    'count'   => 0,
                    'amount'  => 0,
                    'paid'    => 0,
                    'balance' => 0,
                ];
            }

            $byEmployee[$id]['count']   += 1;
            $byEmployee[$id]['amount']  += $invoice->amount;
            $byEmployee[$id]['paid']    += $invoice->paid_amount;
            $byEmployee[$id]['balance'] += $invoice->balance;
        }

        $byEmployee = collect($byEmployee)->sortByDesc('balance');

        $filters = ['Parent/Employee' => $this->employeeName($request->input('employee_id'))];

        return $this->render('pdf.dues', compact('invoices', 'byEmployee', 'filters'), 'dues-report', 'L');
    }

    public function payments(Request $request)
    {
        $this->validateFilters($request);

        $query = Payment::with(['invoice.employee', 'invoice.child'])
            ->where('status', 'approved');

        if ($request->filled('from')) {
            $query->whereDate('paid_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('paid_date', '<=', $request->input('to'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->orderBy('paid_date')->get();

        $filters = [
            'From'   => $request->input('from'),
            'To'     => $request->input('to'),
            'Method' => $request->input('method'),
        ];

        return $this->render('pdf.payments', compact('payments', 'filters'), 'payments-report', 'L');
    }

    public function instruments(Request $request)
    {
        $this->validateFilters($request);

        [$instruments, $filters] = $this->instrumentData($request);

        return $this->render('pdf.instruments', compact('instruments', 'filters'), 'instruments-report');
    }

    public function instrumentCost(Request $request)
    {
        $this->validateFilters($request);

        [$instruments, $filters] = $this->instrumentData($request, true);

        return $this->render('pdf.instrument-cost', compact('instruments', 'filters'), 'instrument-cost-report');
    }

    protected function instrumentData(Request $request, $byCategory = false)
    {
        $query = Instrument::query();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->input('condition'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . Sanitize::like($request->input('search')) . '%');
        }

        if ($byCategory) {
            $query->orderBy('category');
        }

        $instruments = $query->orderBy('name')->get();

        $filters = [
            'Category'  => $request->input('category'),
            'Condition' => $request->input('condition'),
            'Search'    => $request->input('search'),
        ];

        return [$instruments, $filters];
    }

    /** All report filters are validated so odd input never reaches a query. */
    protected function validateFilters(Request $request): void
    {
        $request->validate([
            'search'      => 'nullable|string|max:100',
            'status'      => 'nullable|in:active,inactive,unpaid,partial,paid,overdue',
            'employee_id' => 'nullable|integer',
            'gender'      => 'nullable|in:male,female,other',
            'year'        => 'nullable|integer|min:2000|max:2100',
            'month'       => 'nullable|string|max:20',
            'from'        => 'nullable|date_format:Y-m-d',
            'to'          => 'nullable|date_format:Y-m-d',
            'method'      => 'nullable|in:cash,bank_transfer,card,mobile_banking',
            'category'    => 'nullable|string|max:100',
            'condition'   => 'nullable|in:new,good,fair,damaged',
        ]);
    }

    protected function limitToParent($query)
    {
        if (Auth::user()->isParent()) {
            $employeeId = Auth::user()->employeeId();

            $employeeId
                ? $query->where('employee_id', $employeeId)
                : $query->whereRaw('1 = 0');
        }
    }

    protected function employeeName($employeeId)
    {
        if (!$employeeId) {
            return null;
        }

        return Employee::find((int) $employeeId)?->name;
    }

    /**
     * Shared mPDF renderer. Public so PdfController can reuse it.
     */
    public function render($view, $data, $filename, $orientation = 'P')
    {
        $filters = [];

        if (isset($data['filters'])) {
            foreach ($data['filters'] as $label => $value) {
                if ($value !== null && $value !== '') {
                    $filters[$label] = $value;
                }
            }
        }

        $data['filters'] = $filters;

        $data['code']  = strtoupper(substr($filename, 0, 3)) . '-' . now('Asia/Dhaka')->format('ymd-His');
        $data['qrUrl'] = request()->fullUrl();

        $config   = (new ConfigVariables())->getDefaults();
        $fontData = (new FontVariables())->getDefaults()['fontdata'];

        $fontData['solaimanlipi'] = [
            'R' => 'SolaimanLipi_Bold_10-03-12.ttf',
            'B' => 'SolaimanLipi_Bold_10-03-12.ttf',
        ];

        // Writable temp dir (vendor/mpdf/tmp is often read-only on hosted platforms).
        $tmp = storage_path('app/mpdf');
        if (!is_dir($tmp)) {
            @mkdir($tmp, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'orientation'   => $orientation,
            'margin_top'    => 15,
            'margin_bottom' => 18,
            'margin_left'   => 12,
            'margin_right'  => 12,
            'tempDir'       => $tmp,
            'fontDir'       => array_merge($config['fontDir'], [storage_path('fonts')]),
            'fontdata'      => $fontData,
            'default_font'  => 'solaimanlipi',
        ]);

        $mpdf->SetHTMLFooter(
            '<div style="text-align:center;font-size:8pt;color:#777;">Page {PAGENO} of {nb}</div>'
        );

        $mpdf->WriteHTML(view($view, $data)->render());

        $name = $filename . '-' . now('Asia/Dhaka')->format('Y-m-d') . '.pdf';

        Log::info('Report generated', ['report' => $filename, 'by' => Auth::id()]);

        return response($mpdf->Output($name, 'S'), 200, [
            'Content-Type'           => 'application/pdf',
            'Content-Disposition'    => 'inline; filename="' . $name . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }
}
