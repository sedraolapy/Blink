<?php

namespace App\Http\Resources\Booking;

use App\Enums\ContractStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $types = [];

        if ($this->flexBooking) {
            $types[] = [
                'type' => 'flex',
                'booking_type_id' => $this->flexBooking->id,
                'date' => $this->flexBooking->created_at?->toDateString(),

                'orders' => [
                    'installation' => (bool) $this->flexBooking->installation_order,
                    'extension' => (bool) $this->flexBooking->extension_order,
                ],
            ];
        }

        if ($this->ledBooking) {
            $types[] = [
                'type' => 'electronic',
                'booking_type_id' => $this->ledBooking->id,
                'date' => $this->ledBooking->created_at?->toDateString(),

                'orders' => [
                    'operation' => (bool) $this->ledBooking->operation_order,
                ],
            ];
        }

        if ($this->externalBooking) {
            $types[] = [
                'type' => 'external',
                'booking_type_id' => $this->externalBooking->id,
                'date' => $this->externalBooking->created_at?->toDateString(),

                'orders' => [
                    'installation' => (bool) $this->externalBooking->installation_order,
                    'extension' => (bool) $this->externalBooking->extension_order,
                ],
            ];
        }

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'advertiser_type' => $this->booking_type->value,

            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ],

            'quotation' => $this->quotation !== null,
            'contract' => $this->contract !== null
                && $this->contract->status !== ContractStatusEnum::PENDING,

            'orders' => [
                'installation' => (bool) $this->installation_order,
                'extension' => (bool) $this->extension_order,
                'operation' => (bool) $this->operation_order,
            ],

            'types' => $types,
        ];
    }
}