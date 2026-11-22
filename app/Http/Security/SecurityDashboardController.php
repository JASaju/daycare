<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\SecurityAlert;
use App\Models\SuspiciousActivity;
use App\Models\SystemAuditLog;
use Illuminate\Http\JsonResponse;

class SecurityDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->startOfDay();

        return response()->json([
            'logins_today'       => LoginActivity::success()->where('logged_at', '>=', $today)->count(),
            'failed_today'       => LoginActivity::failed()->where('logged_at', '>=', $today)->count(),
            'open_suspicious'    => SuspiciousActivity::open()->count(),
            'critical_alerts'    => SecurityAlert::where('status', 'new')->where('severity', 'critical')->count(),
            'new_alerts'         => SecurityAlert::where('status', 'new')->count(),
            'audit_events_today' => SystemAuditLog::where('created_at', '>=', $today)->count(),
            'top_failed_ips'     => LoginActivity::failed()
                ->where('logged_at', '>=', now()->subDay())
                ->selectRaw('ip_address, COUNT(*) total')
                ->groupBy('ip_address')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'failed_last_7_days' => LoginActivity::failed()
                ->where('logged_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(logged_at) day, COUNT(*) total')
                ->groupBy('day')
                ->orderBy('day')
                ->get(),
        ]);
    }
}
