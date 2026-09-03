<?php

namespace App\Http\Resources\Customer;

use App\Services\Booking\BookingRequirementsService;
use Illuminate\Http\Request;
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
            
            'status' => $this->contract?->status?->value ?? $this->contract?->status,
            'requirements' => $requirementsService->getRequirements($this->resource),
        ];
    }
}
