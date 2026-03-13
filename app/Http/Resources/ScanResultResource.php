<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rating'              => $this->rating,
            'scanned_for'         => $this->profile_id
                ? new ProfileResource($this->whenLoaded('profile'))
                : new UserResource($this->whenLoaded('user')),
            'product'             => new ProductResource($this->whenLoaded('product')),
            'triggered_allergens' => $this->triggered_allergens ?? [],
            'scanned_at'          => $this->created_at,
        ];
    }
}