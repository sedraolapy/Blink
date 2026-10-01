<?php

namespace App\Http\Requests\Booking\ExternalBooking;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExternalBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::CREATE_EXTERNAL_BOOKING->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
            ],

            'customer_id' => [
                Rule::requiredIf(
                    fn () => empty($this->input('booking_id'))
                ),
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'advertiser_type' => [
                Rule::requiredIf(
                    fn () => empty($this->input('booking_id'))
                ),
                'nullable',
                Rule::in([
                    'local',
                    'foreign',
                ]),
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
                Rule::exists('external_assets','id')
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
                $designs = collect($this->input('designs', []));

                foreach ($this->input('periods', []) as $periodIndex => $period) {
                    foreach ($period['items'] ?? [] as $itemIndex => $item) {

                        $designName = $item['design_name'] ?? null;

                        if (
                            $designName !== null
                            && !$designs->contains($designName)
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