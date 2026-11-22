<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuspiciousActivityResource;
use App\Models\SuspiciousActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SuspiciousActivityController extends Controller
{
    public function index(Request $r): AnonymousResourceCollection
    {
        $q = SuspiciousActivity::with(['user', 'resolver'])->latest('detected_at')
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))
            ->when($r->severity, fn ($q, $v) => $q->where('severity', $v));

        return SuspiciousActivityResource::collection($q->paginate(20));
    }

    public function update(Request $r, SuspiciousActivity $suspicious_activity): SuspiciousActivityResource
    {
        $data = $r->validate([
            'status' => 'required|in:open,investigating,resolved,false_positive',
        ]);

        if (in_array($data['status'], ['resolved', 'false_positive'])) {
            $data['resolved_by'] = $r->user()->id;
            $data['resolved_at'] = now();
        }

        $suspicious_activity->update($data);

        return new SuspiciousActivityResource($suspicious_activity->load(['user', 'resolver']));
    }
}
