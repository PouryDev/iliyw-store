<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'instagram_id' => $this->instagram_id,
            'instagram_username' => $this->instagram_username,
            'is_admin' => (bool) $this->is_admin,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

