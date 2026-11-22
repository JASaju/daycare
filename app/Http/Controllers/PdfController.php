<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PdfController extends Controller
{
    /**
     * Attendance PDF. ?date= &name= &employee_id= &gender= &attendance_status=
     */
    public function attendance(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\Attendance::class);

        $request->validate(AttendanceController::FILTER_RULES);

        $attendance = app(AttendanceController::class);

        $date     = $attendance->resolveDate($request);
        $children = $attendance->filteredQuery($request, $date)->orderBy('name')->get();

        $selectedEmployee = $request->filled('employee_id')
            ? Employee::find((int) $request->input('employee_id'))
            : null;

        $filterStatus = $request->input('attendance_status');

        $filters = [
            'Date'   => $date,
            'Name'   => $request->input('name'),
            'Gender' => $request->input('gender'),
            'Status' => $filterStatus ? str_replace('_', ' ', $filterStatus) : null,
            'Parent' => $selectedEmployee->name ?? 'N/A';
        ];

        return app(ReportController::class)->render(
            'pdf.attendance',
            compact('children', 'date', 'filters'),
            'attendance'
        );
    }
}
