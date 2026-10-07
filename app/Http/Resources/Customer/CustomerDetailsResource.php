<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => [
                'ar' => $this->getTranslation('name', 'ar'),
                'en' => $this->getTranslation('name', 'en'),
            ],

            'phone' => $this->phone,

            'subscription_type' => $this->subscriptions
                ->first()
                ?->subscription_type
                ?->value,

            'contract_status' => $this->contract_status,
            'bookings_count' => $this->bookings_count,
        ];
    }
}