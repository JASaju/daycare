<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Invoice;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isStaff()) {
            $open = ["unpaid", "partial", "overdue"];

            return view("dashboard", [
                "childrenCount"     => Child::where("status", "active")->count(),
                "staffCount"        => Staff::where("status", "active")->count(),
                "todayAttendance"   => Attendance::whereDate("date", today())->whereNotNull("check_in")->count(),
                "unpaidInvoices"    => Invoice::whereIn("status", $open)->count(),
                // eager-load payments to avoid one query per invoice
                "outstandingAmount" => Invoice::with("payments")->whereIn("status", $open)->get()->sum("balance"),
            ]);
        }

        // Parent view: only their own children and invoices
        $employee = $user->employee;

        $children = $employee
            ? $employee->children()->with(["attendances" => fn ($q) => $q->whereDate("date", today())])->get()
            : collect();

        $invoices = $employee
            ? $employee->invoices()->with("payments")->latest()->take(5)->get()
            : collect();

        return view("dashboard", compact("employee", "children", "invoices"));
    }
}
