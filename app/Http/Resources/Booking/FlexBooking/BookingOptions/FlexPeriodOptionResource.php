<?php

namespace App\Http\Resources\Booking\FlexBooking\BookingOptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlexPeriodOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'damascus_daraa_sweida_start_day' => $this->damascus_daraa_sweida_start_day,
            'other_governorates_start_day' => $this->other_governorates_start_day,
            'is_available' => (bool) $this->is_available,
        ];
    }
}