<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => $this->user?->name,
            'email'          => $this->email,
            'status'         => $this->status,
            'failure_reason' => $this->failure_reason,
            'ip_address'     => $this->ip_address,
            'user_agent'     => $this->user_agent,
            'logged_at'      => $this->logged_at?->toDateTimeString(),
        ];
    }
}
