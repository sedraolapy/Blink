<?php

namespace App\Http\Resources\Quotation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->resource['booking'];
        $calculation = $this->resource['calculation'];

        return [
            'customer' => [
                'id' => $booking->customer->id,
                'name' => $booking->customer->name,
            ],

            'advertiser_type' =>
                $booking->booking_type->value,

            'quotation' => [
                'last_issued_at' =>
                    $booking->quotation
                        ?->updated_at
                        ?->toISOString(),
            ],

            'flex' => $calculation['flex'],
            'electronic' => $calculation['electronic'],
            'outdoor' => $calculation['outdoor'],

            'grand_total' =>
                $calculation['grand_total'],
        ];
    }
}