@extends("layouts.app")
@section("title", "Attendance")
@section("content")

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-calendar-check me-1"></i> Attendance
        </h3>
        <div class="d-flex gap-2 align-items-center">
            @if(in_array(auth()->user()->role, ["admin", "staff"]))
                <a href="{{ route('attendance.pdf', request()->query()) }}" target="_blank"
                   class="btn btn-danger" title="Generate attendance PDF">
                    <i class="bi bi-file-earmark-pdf"></i> Attendance PDF
                </a>
            @endif
        </div>
    </div>

    {{-- Validation errors (for example a future date) --}}
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    {{-- FILTER --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-funnel me-1"></i> Attendance Filter</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('attendance.index') }}" method="GET">
                <div class="row g-3">

                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control"
                               value="{{ $date }}" max="{{ today()->toDateString() }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Child Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ request('name') }}" placeholder="Search child...">
                    </div>

                    @if(!auth()->user()->isParent())
                        <div class="col-md-3">
                            <label class="form-label">Employee / Parent</label>
                            <select name="employee_id" class="form-select">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                            {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">All</option>
                            <option value="male"   {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other"  {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Attendance Status</label>
                        <select name="attendance_status" class="form-select">
                            <option value="">All</option>
                            <option value="not_checked_in" {{ request('attendance_status') == 'not_checked_in' ? 'selected' : '' }}>Not Checked In</option>
                            <option value="checked_in"     {{ request('attendance_status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="checked_out"    {{ request('attendance_status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="still_inside"   {{ request('attendance_status') == 'still_inside' ? 'selected' : '' }}>Still Inside</option>
                        </select>
                    </div>

                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ACTIVE FILTER INFORMATION --}}
    @if(
        request()->filled('name') ||
        request()->filled('employee_id') ||
        request()->filled('gender') ||
        request()->filled('attendance_status')
    )
        <div class="alert alert-info py-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel-fill me-2"></i>
                <div>
                    <strong>Filters applied</strong>

                    @if(request()->filled('name'))
                        <span class="badge bg-primary ms-2">Name: {{ request('name') }}</span>
                    @endif

                    @if(request()->filled('gender'))
                        <span class="badge bg-secondary ms-2">Gender: {{ ucfirst(request('gender')) }}</span>
                    @endif

                    @if(request()->filled('attendance_status'))
                        <span class="badge bg-success ms-2">
                            Status:
                            @if(request('attendance_status') == 'not_checked_in') Not Checked In
                            @elseif(request('attendance_status') == 'checked_in') Checked In
                            @elseif(request('attendance_status') == 'checked_out') Checked Out
                            @elseif(request('attendance_status') == 'still_inside') Still Inside
                            @endif
                        </span>
                    @endif

                    @if(request()->filled('employee_id'))
                        @php
                            $selectedEmployee = $employees->firstWhere('id', request('employee_id'));
                        @endphp
                        @if($selectedEmployee)
                            <span class="badge bg-dark ms-2">Parent: {{ $selectedEmployee->name }}</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ATTENDANCE LIST --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-calendar-check me-1"></i> Attendance List</strong>

            <span class="text-muted small">
                Date: <strong>{{ \Carbon\Carbon::parse($date)->format("d/m/Y") }}</strong>
                <span class="mx-1">|</span>
                Total: <strong>{{ $children->count() }}</strong>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Child Name</th>
                    <th>Parent / Employee</th>
                    <th>Gender</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>

                <tbody>
                @forelse($children as $child)
                    @php $attendance = $child->attendances->first(); @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <strong>{{ $child->name }}</strong>
                            @if(isset($child->age))
                                <div class="text-muted small">Age: {{ $child->age }}</div>
                            @endif
                        </td>

                        <td>
                            @if($child->employee)
                                {{ $child->employee->name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($child->gender)
                                {{ ucfirst($child->gender) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($attendance && $attendance->check_in)
                                <span class="badge bg-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    {{ \Carbon\Carbon::parse($attendance->check_in, "UTC")->setTimezone("Asia/Dhaka")->format("g:i A") }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($attendance && $attendance->check_out)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    {{ \Carbon\Carbon::parse($attendance->check_out, "UTC")->setTimezone("Asia/Dhaka")->format("g:i A") }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if(!$attendance || !$attendance->check_in)
                                <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> Not Checked In</span>
                            @elseif($attendance->check_in && !$attendance->check_out)
                                <span class="badge bg-primary"><i class="bi bi-person-check me-1"></i> Still Inside</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Checked Out</span>
                            @endif
                        </td>

                        <td class="text-end">
                            @if(in_array(auth()->user()->role, ["admin", "staff"]))

                                <form action="{{ route('attendance.check-in', $child) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <button type="submit" class="btn btn-sm btn-success"
                                            {{ $attendance && $attendance->check_in ? 'disabled' : '' }}>
                                        <i class="bi bi-box-arrow-in-right"></i> Check In
                                    </button>
                                </form>

                                <form action="{{ route('attendance.check-out', $child) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <button type="submit" class="btn btn-sm btn-warning"
                                            {{ !$attendance || !$attendance->check_in || $attendance->check_out ? 'disabled' : '' }}>
                                        <i class="bi bi-box-arrow-right"></i> Check Out
                                    </button>
                                </form>

                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            <div class="fw-semibold">No children found.</div>
                            <div class="small mt-1">Try changing your filters.</div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SUMMARY --}}
    @if($children->count() > 0)
        @php
            $totalChildren = $children->count();

            $checkedIn = $children->filter(function ($child) {
                $a = $child->attendances->first();
                return $a && $a->check_in;
            })->count();

            $checkedOut = $children->filter(function ($child) {
                $a = $child->attendances->first();
                return $a && $a->check_out;
            })->count();

            $stillInside = $children->filter(function ($child) {
                $a = $child->attendances->first();
                return $a && $a->check_in && !$a->check_out;
            })->count();
        @endphp

        <div class="row mt-3">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Total Children</div>
                        <h3 class="mb-0">{{ $totalChildren }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Checked In</div>
                        <h3 class="mb-0 text-success">{{ $checkedIn }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Still Inside</div>
                        <h3 class="mb-0 text-primary">{{ $stillInside }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Checked Out</div>
                        <h3 class="mb-0 text-success">{{ $checkedOut }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection