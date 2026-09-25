<?php

namespace App\Http\Requests\Booking\ExternalBooking;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExternalAvailableAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $bookingId = $this->input('booking_id');

        if (!$bookingId) {
            return $user->can(
                PermissionEnum::CREATE_EXTERNAL_BOOKING->value
            );
        }

        $hasExternalBooking = Booking::query()
            ->whereKey($bookingId)
            ->whereHas('externalBooking')
            ->exists();

        return $user->can(
            $hasExternalBooking
                ? PermissionEnum::UPDATE_BOOKING->value
                : PermissionEnum::CREATE_EXTERNAL_BOOKING->value
        );
    }

    public function rules(): array
    {
        return [
            'booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
            ],

            'type' => [
                'required',
                Rule::enum(ExternalAssetTypeEnum::class),
            ],

            'governorate_id' => [
                'required',
                'integer',
                'exists:governorates,id',
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

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.integer' => __('validation.custom.booking_id.integer'),
            'booking_id.exists' => __('validation.custom.booking_id.exists'),

            'type.required' => __('validation.custom.type.required'),
            'type.enum' => __('validation.custom.type.enum'),

            'governorate_id.required' => __('validation.custom.governorate_id.required'),
            'governorate_id.integer' => __('validation.custom.governorate_id.integer'),
            'governorate_id.exists' => __('validation.custom.governorate_id.exists'),

            'periods.required' => __('validation.custom.periods.required'),
            'periods.array' => __('validation.custom.periods.array'),
            'periods.min' => __('validation.custom.periods.min'),

            'periods.*.start_date.required' => __('validation.custom.period_start_date.required'),
            'periods.*.start_date.date' => __('validation.custom.period_start_date.date'),

            'periods.*.end_date.required' => __('validation.custom.period_end_date.required'),
            'periods.*.end_date.date' => __('validation.custom.period_end_date.date'),
            'periods.*.end_date.after_or_equal' => __('validation.custom.period_end_date.after_or_equal'),

            'search.string' => __('validation.custom.search.string'),
            'search.max' => __('validation.custom.search.max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'booking_id' => __('validation.attributes.booking_id'),
            'type' => __('validation.attributes.type'),
            'governorate_id' => __('validation.attributes.governorate_id'),
            'periods' => __('validation.attributes.periods'),
            'periods.*.start_date' => __('validation.attributes.start_date'),
            'periods.*.end_date' => __('validation.attributes.end_date'),
            'search' => __('validation.attributes.search'),
        ];
    }

}