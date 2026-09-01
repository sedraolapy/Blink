<?php

namespace App\Http\Resources\Electronic;

use App\Models\LedNetwork;
use App\Models\LedScreen;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectronicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof LedScreen) {
            return $this->screenData();
        }

        if ($this->resource instanceof LedNetwork) {
            return $this->networkData();
        }

        return [];
    }

    private function screenData(): array
    {
        return [
            'id' => $this->id,
            'type' => 'screen',
            'name' => $this->location_name,
            'code' => $this->code,

            'governorate' => [
                'id' => $this->area?->governorate?->id,
                'name' => $this->area?->governorate?->name,
            ],

            'area' => $this->area?->name,

            'width' => (float) $this->width,
            'height' => (float) $this->height,

            'pixel_width' => (int) $this->width_px,
            'pixel_height' => (int) $this->height_px,
        ];
    }

    private function networkData(): array
    {
        $firstScreen = $this->screens->first();

        $area = $firstScreen?->area;
        $governorate = $area?->governorate;

        return [
            'id' => $this->id,
            'type' => 'network',
            'name' => $this->location_name,

            'governorate' => [
                'id' => $governorate?->id,
                'name' => $governorate?->name,
            ],

            'area' => $area?->name,

            'screens_count' => (int) $this->screens_count,
        ];
    }
}
