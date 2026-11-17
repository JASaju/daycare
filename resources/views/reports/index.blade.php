@extends('layouts.app')

@section('title', 'Reports')

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();

    $statusOpts    = ['active' => 'Active', 'inactive' => 'Inactive'];
    $genderOpts    = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
    $invStatusOpts = ['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue'];
    $methodOpts    = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'mobile_banking' => 'Mobile Banking'];
    $condOpts      = ['new' => 'New', 'good' => 'Good', 'fair' => 'Fair', 'damaged' => 'Damaged'];
    $employeeOpts  = $employees->pluck('name', 'id')->all();
    $categoryOpts  = [];
    foreach ($categories as $c) {
        $categoryOpts[$c] = $c;
    }

    // [name, label, type, options, default]
    $reports = [
        [
            'route'  => 'reports.children',
            'icon'   => 'bi-people',
            'title'  => 'Children Report',
            'desc'   => 'All enrolled children with age, gender and parent.',
            'fields' => [
                ['status', 'Status', 'select', $statusOpts],
                ['gender', 'Gender', 'select', $genderOpts],
                ['employee_id', 'Parent / Employee', 'select', $employeeOpts],
                ['search', 'Child name', 'text'],
            ],
        ],
        [
            'route'  => 'reports.invoices',
            'icon'   => 'bi-receipt',
            'title'  => 'Invoices Report',
            'desc'   => 'All invoices with paid amount, balance and status.',
            'fields' => [
                ['status', 'Status', 'select', $invStatusOpts],
                ['employee_id', 'Parent / Employee', 'select', $employeeOpts],
                ['year', 'Year', 'number'],
                ['month', 'Month', 'text'],
            ],
        ],
        [
            'route'  => 'reports.dues',
            'icon'   => 'bi-cash-coin',
            'title'  => 'Outstanding Dues',
            'desc'   => 'Unpaid, partial and overdue invoices with a summary per parent.',
            'fields' => [
                ['employee_id', 'Parent / Employee', 'select', $employeeOpts],
            ],
        ],
        [
            'route'  => 'reports.payments',
            'icon'   => 'bi-wallet2',
            'title'  => 'Payments Report',
            'desc'   => 'Payments received, with totals by method.',
            'fields' => [
                ['from', 'From', 'date'],
                ['to', 'To', 'date'],
                ['method', 'Method', 'select', $methodOpts],
            ],
        ],
        [
            'route'  => 'attendance.pdf',
            'icon'   => 'bi-calendar-check',
            'title'  => 'Attendance Report',
            'desc'   => 'Daily attendance (check-in / check-out).',
            'fields' => [
                ['date', 'Date', 'date', null, today()->toDateString()],
                ['attendance_status', 'Attendance', 'select', [
                    'not_checked_in' => 'Not checked in',
                    'checked_in'     => 'Checked in',
                    'checked_out'    => 'Checked out',
                    'still_inside'   => 'Still inside',
                ]],
                ['employee_id', 'Parent / Employee', 'select', $employeeOpts],
                ['gender', 'Gender', 'select', $genderOpts],
            ],
        ],
        [
            'route'  => 'reports.instruments',
            'icon'   => 'bi-box-seam',
            'title'  => 'Instruments & Toys',
            'desc'   => 'Inventory with quantity, condition and value.',
            'fields' => [
                ['category', 'Category', 'select', $categoryOpts],
                ['condition', 'Condition', 'select', $condOpts],
                ['search', 'Item name', 'text'],
            ],
        ],
    ];

    if ($isAdmin) {
        $reports[] = [
            'route'  => 'reports.employees',
            'icon'   => 'bi-person-badge',
            'title'  => 'Employees Report',
            'desc'   => 'All employees (parents) with number of children.',
            'fields' => [['search', 'Name / Code', 'text']],
        ];
        $reports[] = [
            'route'  => 'reports.staff',
            'icon'   => 'bi-person-workspace',
            'title'  => 'Staff Report',
            'desc'   => 'All staff members with position and status.',
            'fields' => [['status', 'Status', 'select', $statusOpts]],
        ];
    }
@endphp

<div class="mb-4">
    <h4 class="mb-1"><i class="bi bi-file-earmark-pdf me-2"></i>Reports</h4>

</div>

<div class="row g-3">
    @foreach ($reports as $r)
        <div class="col-md-6 col-xl-4">
            <form class="card h-100" method="GET" action="{{ route($r['route']) }}" target="_blank">
                <div class="card-body d-flex flex-column">
                    <h6 class="mb-1"><i class="bi {{ $r['icon'] }} me-2"></i>{{ $r['title'] }}</h6>
                    <div class="text-muted small mb-3">{{ $r['desc'] }}</div>

                    <div class="row g-2 mb-3">
                        @foreach ($r['fields'] as $f)
                            @php
                                $name = $f[0]; $label = $f[1]; $type = $f[2];
                                $options = $f[3] ?? null; $default = $f[4] ?? null;
                                $half = in_array($type, ['date', 'number']);
                            @endphp
                            <div class="{{ $half ? 'col-6' : 'col-12' }}">
                                <label class="form-label small mb-1">{{ $label }}</label>
                                @if ($type === 'select')
                                    <select name="{{ $name }}" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        @foreach ($options as $value => $text)
                                            <option value="{{ $value }}">{{ $text }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $type }}" name="{{ $name }}" value="{{ $default }}" class="form-control form-control-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-danger btn-sm mt-auto">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Generate PDF
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection