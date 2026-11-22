<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserActivityLogResource;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserActivityLogController extends Controller
{
    public function index(Request $r): AnonymousResourceCollection
    {
        $q = UserActivityLog::with('user')->latest()
            ->when($r->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($r->module, fn ($q, $v) => $q->where('module', $v))
            ->when($r->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($r->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        return UserActivityLogResource::collection($q->paginate(20));
    }
}
