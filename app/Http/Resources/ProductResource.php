<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'barcode'          => $this->barcode,
            'name'             => $this->name,
            'brand'            => $this->brand,
            'image_url'        => $this->image_url,
            'ingredients_text' => $this->ingredients_text,
            'allergens'        => AllergenResource::collection($this->whenLoaded('allergens')),
        ];
    }
}