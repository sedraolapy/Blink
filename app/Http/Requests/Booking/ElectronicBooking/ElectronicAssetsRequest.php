<?php

namespace App\Http\Requests\Booking\ElectronicBooking;

use App\Enums\PermissionEnum;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ElectronicAssetsRequest extends FormRequest
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
                PermissionEnum::CREATE_SCREEN_BOOKING->value
            );
        }

        $hasElectronicBooking = Booking::query()
            ->whereKey($bookingId)
            ->whereHas('ledBooking')
            ->exists();

        return $user->can(
            $hasElectronicBooking
                ? PermissionEnum::UPDATE_BOOKING->value
                : PermissionEnum::CREATE_SCREEN_BOOKING->value
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

            'governorate_id' => [
                'required',
                'integer',
                'exists:governorates,id',
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
            'governorate_id.required' =>
                __('validation.electronic_assets.governorate_id.required'),

            'governorate_id.integer' =>
                __('validation.electronic_assets.governorate_id.integer'),

            'governorate_id.exists' =>
                __('validation.electronic_assets.governorate_id.exists'),

            'search.string' =>
                __('validation.electronic_assets.search.string'),

            'search.max' =>
                __('validation.electronic_assets.search.max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'governorate_id' =>
                __('validation.attributes.governorate_id'),

            'search' =>
                __('validation.attributes.search'),
        ];
    }
}