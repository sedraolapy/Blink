<?php

namespace App\Services\Booking;

use App\Models\Booking;

class BookingRequirementsService
{
    public function getRequirements(Booking $booking): array
    {
        $requirements = [
            'price_offer' => $this->hasPriceOffer($booking),
            'contract' => $this->hasContractImage($booking),
        ];


        if ($booking->requiresInstallationAndExtension())
        {
            $requirements['installation_order'] = (bool) $booking->installation_order;
            $requirements['extension_order'] = (bool) $booking->extension_order;
        }

        if ($booking->requiresOperation())
        {
            $requirements['operation_order'] = (bool) $booking->operation_order;
        }

        return $requirements;
    }

    private function hasPriceOffer(Booking $booking): bool
    {
        return $booking->relationLoaded('quotation')
            ? $booking->quotation !== null
            : $booking->quotation()->exists();
    }

    private function hasContractImage(Booking $booking): bool
    {
        $contract = $booking->contract;

        if (! $contract) {
            return false;
        }

        return $contract->hasMedia('contract_images');
    }
}