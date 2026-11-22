<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginActivityResource;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoginActivityController extends Controller
{
    public function index(Request $r): AnonymousResourceCollection
    {
        $q = LoginActivity::with('user')->latest('logged_at')
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))
            ->when($r->ip, fn ($q, $v) => $q->where('ip_address', $v))
            ->when($r->search, fn ($q, $v) => $q->where('email', 'like', "%$v%"));

        return LoginActivityResource::collection($q->paginate(20));
    }

    public function failed(Request $r): AnonymousResourceCollection
    {
        $q = LoginActivity::failed()->with('user')->latest('logged_at')
            ->when($r->ip, fn ($q, $v) => $q->where('ip_address', $v))
            ->when($r->search, fn ($q, $v) => $q->where('email', 'like', "%$v%"));

        return LoginActivityResource::collection($q->paginate(20));
    }
}
