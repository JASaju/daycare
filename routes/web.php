<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Route parameter constraints (IDs must be numeric)
|--------------------------------------------------------------------------
*/
Route::pattern('child', '[0-9]+');
Route::pattern('emergencyContact', '[0-9]+');
Route::pattern('instrument', '[0-9]+');
Route::pattern('invoice', '[0-9]+');
Route::pattern('payment', '[0-9]+');
Route::pattern('employee', '[0-9]+');
Route::pattern('staffMember', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Guest (rate limited to slow brute force / mass sign-ups)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // AuthController::register MUST force role = 'parent' (never read role from input).
    // If parents are created by admin only, delete these two routes instead.
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Children: list (policy filters parents to their own children)
    Route::get('/children', [ChildController::class, 'index'])->name('children.index');

    // The old /children/{child}/photo route is REMOVED (photos now come from Cloudinary).

    /*
    |----------------------------------------------------------------------
    | Admin + Staff
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,staff')->group(function () {

        Route::get('/children/create', [ChildController::class, 'create'])->name('children.create');
        Route::post('/children', [ChildController::class, 'store'])->name('children.store');
        Route::get('/children/{child}/edit', [ChildController::class, 'edit'])->name('children.edit');
        Route::put('/children/{child}', [ChildController::class, 'update'])->name('children.update');
        Route::delete('/children/{child}', [ChildController::class, 'destroy'])->name('children.destroy');

        // Emergency contacts: scopeBindings() guarantees {emergencyContact} belongs to {child}
        Route::scopeBindings()->group(function () {
            Route::post('/children/{child}/emergency-contacts', [EmergencyContactController::class, 'store'])
                ->name('emergency-contacts.store');

            Route::delete('/children/{child}/emergency-contacts/{emergencyContact}', [EmergencyContactController::class, 'destroy'])
                ->name('emergency-contacts.destroy');
        });

        // Health records
        Route::get('/children/{child}/health', [HealthRecordController::class, 'edit'])->name('health.edit');
        Route::put('/children/{child}/health', [HealthRecordController::class, 'update'])->name('health.update');

        // Attendance actions
        Route::post('/attendance/{child}/check-in', [AttendanceController::class, 'checkIn'])
            ->middleware('throttle:60,1')
            ->name('attendance.check-in');

        Route::post('/attendance/{child}/check-out', [AttendanceController::class, 'checkOut'])
            ->middleware('throttle:60,1')
            ->name('attendance.check-out');

        // PDFs / reports (heavy: throttled to prevent resource-exhaustion DoS)
        Route::middleware('throttle:20,1')->group(function () {

            Route::get('/attendance/pdf', [PdfController::class, 'attendance'])->name('attendance.pdf');

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('/children', [ReportController::class, 'children'])->name('children');
                Route::get('/invoices', [ReportController::class, 'invoices'])->name('invoices');
                Route::get('/dues', [ReportController::class, 'dues'])->name('dues');
                Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
                Route::get('/instruments', [ReportController::class, 'instruments'])->name('instruments');
                Route::get('/instrument-cost', [ReportController::class, 'instrumentCost'])->name('instrument-cost');
            });
        });

        // Instruments
        Route::get('/instruments', [InstrumentController::class, 'index'])->name('instruments.index');
        Route::get('/instruments/create', [InstrumentController::class, 'create'])->name('instruments.create');
        Route::post('/instruments', [InstrumentController::class, 'store'])->name('instruments.store');
        Route::get('/instruments/{instrument}/edit', [InstrumentController::class, 'edit'])->name('instruments.edit');
        Route::put('/instruments/{instrument}', [InstrumentController::class, 'update'])->name('instruments.update');
        Route::delete('/instruments/{instrument}', [InstrumentController::class, 'destroy'])->name('instruments.destroy');

        // Payments: record / approve / reject (financial actions: throttled)
        Route::middleware('throttle:30,1')->group(function () {

            Route::post('/billing/{invoice}/payments', [InvoiceController::class, 'recordPayment'])
                ->name('billing.payments.store');

            Route::post('/billing/payments/{payment}/approve', [InvoiceController::class, 'approvePayment'])
                ->name('billing.payments.approve');

            Route::post('/billing/payments/{payment}/reject', [InvoiceController::class, 'rejectPayment'])
                ->name('billing.payments.reject');
        });
    });

    /*
    |----------------------------------------------------------------------
    | All authenticated users: controllers MUST authorize per record
    | (Gate::authorize('view', $invoice) etc.), because parents reach these.
    |----------------------------------------------------------------------
    */
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/billing', [InvoiceController::class, 'index'])->name('billing.index');
    Route::get('/billing/{invoice}', [InvoiceController::class, 'show'])->name('billing.show');

    // Parent submits a payment / transaction ID for approval
    Route::post('/billing/{invoice}/submit-payment', [InvoiceController::class, 'submitPayment'])
        ->middleware('throttle:10,1')
        ->name('billing.submitPayment');

    /*
    |----------------------------------------------------------------------
    | Admin only
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        Route::resource('employees', EmployeeController::class)->except(['show']);

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staffMember}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staffMember}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staffMember}', [StaffController::class, 'destroy'])->name('staff.destroy');

        Route::get('/billings/create', [InvoiceController::class, 'create'])->name('billing.create');
        Route::post('/billing', [InvoiceController::class, 'store'])->name('billing.store');
        Route::delete('/billing/{invoice}', [InvoiceController::class, 'destroy'])->name('billing.destroy');

        Route::middleware('throttle:20,1')->prefix('reports')->name('reports.')->group(function () {
            Route::get('/employees', [ReportController::class, 'employees'])->name('employees');
            Route::get('/staff', [ReportController::class, 'staff'])->name('staff');
        });
    });

    // Child details: keep last among child routes so it never shadows /children/create
    Route::get('/children/{child}', [ChildController::class, 'show'])->name('children.show');
});