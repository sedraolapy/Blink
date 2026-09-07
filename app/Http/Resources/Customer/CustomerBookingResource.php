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
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'booking_type' => $this->booking_type instanceof BookingTypeEnum
                ? $this->booking_type->label()
                : BookingTypeEnum::tryFrom($this->booking_type)?->label(),


            'status' => $this->status?->label(),
            'requirements' => $requirementsService->getRequirements($this->resource),
        ];
    }
}
