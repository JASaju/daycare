<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => $this->user?->name,
            'action'      => $this->action,
            'module'      => $this->module,
            'description' => $this->description,
            'method'      => $this->method,
            'url'         => $this->url,
            'ip_address'  => $this->ip_address,
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}
