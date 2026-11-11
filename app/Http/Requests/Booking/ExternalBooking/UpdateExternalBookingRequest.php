<?php

namespace App\Http\Requests\Booking\ExternalBooking;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExternalBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::UPDATE_BOOKING->value
        ) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'booking_id' => $this->route('booking_id'),
            'type' => $this->route('type'),
        ]);
    }

    public function rules(): array
    {
        return [
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id',
            ],

            'type' => [
                'required',
                Rule::enum(ExternalAssetTypeEnum::class),
            ],

            'designs' => [
                'present',
                'array',
            ],

            'designs.*' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'periods' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.start_date' => [
                'required',
                'date',
            ],

            'periods.*.end_date' => [
                'required',
                'date',
                'after_or_equal:periods.*.start_date',
            ],

            'periods.*.items' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.items.*.asset_id' => [
                'required',
                'integer',
                Rule::exists('external_assets', 'id')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'type',
                                $this->input('type')
                            )
                    ),
            ],

            'periods.*.items.*.is_gift' => [
                'required',
                'boolean',
            ],

            'periods.*.items.*.design_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $periods = $this->input('periods', []);

                if (! is_array($periods)) {
                    return;
                }

                $designs = collect(
                    is_array($this->input('designs'))
                        ? $this->input('designs')
                        : []
                );

                foreach ($periods as $periodIndex => $period) {
                    if (! is_array($period)) {
                        continue;
                    }
                    $items = $period['items'] ?? [];

                    if (! is_array($items)) {
                        continue;
                    }

                    $assetIds = [];

                    foreach ($items as $itemIndex => $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $assetId = $item['asset_id'] ?? null;

                        if ($assetId === null || ! is_numeric($assetId)) {
                            continue;
                        }

                        $assetId = (int) $assetId;

                        if (in_array($assetId, $assetIds, true)) {
                            $validator->errors()->add(
                                "periods.{$periodIndex}.items.{$itemIndex}.asset_id",
                                __('validation.custom.external_duplicate_asset_in_period')
                            );
                        }

                        $assetIds[] = $assetId;
                    }

                    foreach ($items as $itemIndex => $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $designName = $item['design_name'] ?? null;

                        if (
                            $designName !== null
                            && ! $designs->contains($designName)
                        ) {
                            $validator->errors()->add(
                                "periods.{$periodIndex}.items.{$itemIndex}.design_name",
                                __('validation.custom.design_name_not_found')
                            );
                        }
                    }
                }
            },
        ];
    }
}