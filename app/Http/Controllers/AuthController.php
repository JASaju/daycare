<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        // No public statistics on the login page (information disclosure).
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email"    => ["required", "email", "max:255"],
            "password" => ["required", "string", "max:255"],
        ]);

        // Throttle per email + IP (the route also has a per-IP throttle).
        $key = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                "email" => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput("email");
        }

        if (Auth::attempt($credentials, $request->boolean("remember"))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            $user = Auth::user();

            // Deactivated staff must not be able to log in.
            if ($user->isStaff() && $user->staff && $user->staff->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    "email" => "This account has been deactivated.",
                ])->onlyInput("email");
            }

            return redirect()->intended(route("dashboard"));
        }

        RateLimiter::hit($key, 300);

        Log::warning('Failed login', ['email' => $credentials['email'], 'ip' => $request->ip()]);

        return back()->withErrors([
            "email" => "The provided credentials do not match our records.",
        ])->onlyInput("email");
    }

    public function showRegister()
    {
        return view("auth.register");
    }

    public function register(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            "name"     => ["required", "string", "max:255"],
            "email"    => ["required", "string", "email", "max:255", "unique:users,email"],
            "password" => ["required", "confirmed", Rules\Password::defaults()],
        ]);

        // role is NOT mass-assignable; always "parent" for self-registration.
        $user = new User([
            "name"     => $validated["name"],
            "email"    => $validated["email"],
            "password" => $validated["password"], // hashed by the model cast
        ]);
        $user->role = "parent";
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route("dashboard");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login");
    }
}
