<?php

namespace App\Http\Requests\Booking\FlexBooking\BookingOptions;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlexPeriodOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->filled('booking_id')
            ? PermissionEnum::UPDATE_BOOKING->value
            : PermissionEnum::CREATE_FLEX_BOOKING->value;

        return $this->user()?->can($permission) ?? false;
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