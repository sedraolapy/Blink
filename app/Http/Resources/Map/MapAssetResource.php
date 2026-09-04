<?php

namespace App\Http\Resources\Map;

use App\Models\ExternalAsset;
use App\Models\FlexBillboard;
use App\Models\LedScreen;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->location_name,
            'type' => $this->getAssetType(),
            'code' => $this->code,

            'governorate' => [
                'id' => $this->area?->governorate?->id,
                'name' => $this->area?->governorate?->name,
            ],
            'area' => $this->area?->name,

            'width' => (float) $this->width,
            'height' => (float) $this->height,

            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,

            'video' => $this->getVideoUrl(),
        ];


        if ($this->resource instanceof LedScreen) {
            $data['pixel_width'] = $this->width_px;
            $data['pixel_height'] = $this->height_px;

            $data['network'] = $this->network
                ? [
                    'id' => $this->network->id,
                    'name' => $this->network->location_name,
                ]
                : null;
        }

        return $data;
    }

    private function getAssetType(): string
    {
        if ($this->resource instanceof FlexBillboard) {
            return 'flex';
        }

        if ($this->resource instanceof LedScreen) {
            return 'electronic';
        }

        if ($this->resource instanceof ExternalAsset) {
            return $this->resource->type instanceof \BackedEnum
                ? $this->resource->type->value
                : $this->resource->type;
        }

        return '';
    }

    private function getVideoUrl(): ?string
    {
        if ($this->resource instanceof FlexBillboard) {
            return $this->getFirstMediaUrl(
                'flex_billboard_video'
            ) ?: null;
        }

        if ($this->resource instanceof LedScreen) {
            return $this->getFirstMediaUrl(
                'led_screen_video'
            ) ?: null;
        }

        if ($this->resource instanceof ExternalAsset) {
            return $this->getFirstMediaUrl(
                'external_asset_video'
            ) ?: null;
        }

        return null;
    }
}
