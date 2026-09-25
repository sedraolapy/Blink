<?php

namespace App\Http\Requests\Booking\FlexBooking\BookingOptions;

use App\Enums\PermissionEnum;
use App\Models\Booking;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlexPeriodOptionsRequest extends FormRequest
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
                PermissionEnum::CREATE_FLEX_BOOKING->value
            );
        }

        $hasFlexBooking = Booking::query()
            ->whereKey($bookingId)
            ->whereHas('flexBooking')
            ->exists();

        return $user->can(
            $hasFlexBooking
                ? PermissionEnum::UPDATE_BOOKING->value
                : PermissionEnum::CREATE_FLEX_BOOKING->value
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
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.integer' =>
                __('validation.integer'),

            'booking_id.exists' =>
                __('validation.exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'booking_id' =>
                __('validation.attributes.booking_id'),
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            )
        );
    }
}