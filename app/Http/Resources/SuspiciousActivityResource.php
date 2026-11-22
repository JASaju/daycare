<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuspiciousActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => $this->user?->name,
            'type'        => $this->type,
            'severity'    => $this->severity,
            'description' => $this->description,
            'ip_address'  => $this->ip_address,
            'status'      => $this->status,
            'detected_at' => $this->detected_at?->toDateTimeString(),
            'resolved_by' => $this->resolver?->name,
            'resolved_at' => $this->resolved_at?->toDateTimeString(),
        ];
    }
}
