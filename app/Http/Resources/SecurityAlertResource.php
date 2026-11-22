<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'message'         => $this->message,
            'severity'        => $this->severity,
            'source'          => $this->source,
            'status'          => $this->status,
            'acknowledged_by' => $this->acknowledger?->name,
            'acknowledged_at' => $this->acknowledged_at?->toDateTimeString(),
            'created_at'      => $this->created_at?->toDateTimeString(),
        ];
    }
}
