<?php

namespace App\Http\Controllers;

use App\Http\Resources\SecurityAlertResource;
use App\Models\SecurityAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SecurityAlertController extends Controller
{
    public function index(Request $r): AnonymousResourceCollection
    {
        $q = SecurityAlert::with('acknowledger')->latest()
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))
            ->when($r->severity, fn ($q, $v) => $q->where('severity', $v));

        return SecurityAlertResource::collection($q->paginate(20));
    }

    public function update(Request $r, SecurityAlert $security_alert): SecurityAlertResource
    {
        $data = $r->validate([
            'status' => 'required|in:acknowledged,closed',
        ]);

        $security_alert->update($data + [
            'acknowledged_by' => $r->user()->id,
            'acknowledged_at' => now(),
        ]);

        return new SecurityAlertResource($security_alert->load('acknowledger'));
    }
}
