<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScanLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'rating'              => $this->rating,
            'scanned_for'         => $this->profile_id
                ? new ProfileResource($this->whenLoaded('profile'))
                : null,
            'product'             => new ProductResource($this->whenLoaded('product')),
            'triggered_allergens' => $this->triggered_allergens ?? [],
            'scanned_at'          => $this->created_at,
        ];
    }
}