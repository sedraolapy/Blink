<?php

namespace App\Http\Resources\AdvertisingPeriod;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisingPeriodResource extends JsonResource
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
            'number' => $this->number,
            'is_current' => (bool) $this->is_current,
            'damascus_start_date' => $this->damascus_start_date,
            'damascus_end_date' => $this->damascus_end_date,
        ];
    }
}
