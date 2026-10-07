<?php

namespace App\Http\Resources\Contract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'contract_number' => $this->contract_number,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),

            'contract_images' => $this
                ->getMedia('contract_images')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ])
                ->values(),

            'attachment_images' => $this
                ->getMedia('attachment_images')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ])
                ->values(),
        ];
    }
}