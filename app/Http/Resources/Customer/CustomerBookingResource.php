<?php

namespace App\Http\Resources\Customer;

use App\Services\Booking\BookingRequirementsService;
use Illuminate\Http\Request;
use App\Enums\BookingTypeEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $requirementsService = app(BookingRequirementsService::class);

        return [
            'id' => $this->id,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'booking_type' => $this->booking_type,


            'status' => $this->status,
            'requirements' => $requirementsService->getRequirements($this->resource),
        ];
    }
}
