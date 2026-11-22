<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use App\Models\SecurityAlert;
use App\Models\SuspiciousActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class SecurityEventSubscriber
{
    public function onLogin(Login $e): void
    {
        LoginActivity::create([
            'user_id'    => $e->user->id,
            'email'      => $e->user->email,
            'status'     => 'success',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_at'  => now(),
        ]);
    }

    public function onLogout(Logout $e): void
    {
        if ($e->user) {
            LoginActivity::create([
                'user_id'    => $e->user->id,
                'email'      => $e->user->email,
                'status'     => 'logout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at'  => now(),
            ]);
        }
    }

    public function onFailed(Failed $e): void
    {
        $ip = request()->ip();

        LoginActivity::create([
            'user_id'        => $e->user?->id,
            'email'          => $e->credentials['email'] ?? null,
            'status'         => 'failed',
            'failure_reason' => $e->user ? 'Wrong password' : 'Unknown email',
            'ip_address'     => $ip,
            'user_agent'     => request()->userAgent(),
            'logged_at'      => now(),
        ]);

        // Brute-force rule: 5+ failures from one IP in 10 minutes
        $count = LoginActivity::failed()
            ->where('ip_address', $ip)
            ->where('logged_at', '>=', now()->subMinutes(10))
            ->count();

        if (
            $count >= 5
            && ! SuspiciousActivity::open()
                ->where('ip_address', $ip)
                ->where('type', 'brute_force')
                ->exists()
        ) {
            $s = SuspiciousActivity::create([
                'type'        => 'brute_force',
                'severity'    => 'high',
                'ip_address'  => $ip,
                'description' => "$count failed logins in 10 minutes",
                'detected_at' => now(),
            ]);

            SecurityAlert::create([
                'title'                  => 'Possible brute-force attack',
                'message'                => "IP $ip: $count failed logins",
                'severity'               => 'high',
                'source'                 => 'login_monitor',
                'suspicious_activity_id' => $s->id,
            ]);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class  => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
        ];
    }
}
