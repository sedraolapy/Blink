<?php

namespace App\Http\Resources\Outdoor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutdoorResource extends JsonResource
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
            'type' => $this->type?->value ?? $this->type,
            'name' => $this->location_name,
            'code' => $this->code,

            'governorate' => [
                'id' => $this->area?->governorate?->id,
                'name' => $this->area?->governorate?->name,
            ],

            'area' => $this->area?->name,

            'width' => (float) $this->width,
            'height' => (float) $this->height,

            'status' => $this->availability_status,
        ];
    }
}
