<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reference implementation for  ->middleware('role:admin,staff').
 * If your existing RoleMiddleware already behaves like this (403 for any
 * role not listed, including no user), keep yours.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
