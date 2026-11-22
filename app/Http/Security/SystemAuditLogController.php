<?php

namespace App\Http\Controllers;

use App\Http\Resources\SystemAuditLogResource;
use App\Models\SystemAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemAuditLogController extends Controller
{
    public function index(Request $r): AnonymousResourceCollection
    {
        $q = SystemAuditLog::with('user')->latest()
            ->when($r->event, fn ($q, $v) => $q->where('event', $v))
            ->when($r->auditable_type, fn ($q, $v) => $q->where('auditable_type', 'like', "%$v"))
            ->when($r->user_id, fn ($q, $v) => $q->where('user_id', $v));

        return SystemAuditLogResource::collection($q->paginate(20));
    }
}
