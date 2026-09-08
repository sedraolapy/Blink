<?php

namespace App\Http\Requests\Booking\FlexBooking\BookingOptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlexAvailableAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

            'period_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'period_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:advertising_periods,id',
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
            'booking_id.integer' =>
                __('validation.integer'),

            'booking_id.exists' =>
                __('validation.exists'),

            'governorate_id.required' =>
                __('validation.required'),

            'governorate_id.integer' =>
                __('validation.integer'),

            'governorate_id.exists' =>
                __('validation.exists'),

            'period_ids.required' =>
                __('validation.required'),

            'period_ids.array' =>
                __('validation.array'),

            'period_ids.min' =>
                __('validation.min.array'),

            'period_ids.*.required' =>
                __('validation.required'),

            'period_ids.*.integer' =>
                __('validation.integer'),

            'period_ids.*.distinct' =>
                __('validation.distinct'),

            'period_ids.*.exists' =>
                __('validation.exists'),

            'search.string' =>
                __('validation.string'),

            'search.max' =>
                __('validation.max.string'),
        ];
    }

    public function attributes(): array
    {
        return [
            'booking_id' =>
                __('validation.attributes.booking_id'),

            'governorate_id' =>
                __('validation.attributes.governorate_id'),

            'period_ids' =>
                __('validation.attributes.period_ids'),

            'period_ids.*' =>
                __('validation.attributes.period_id'),

            'search' =>
                __('validation.attributes.search'),
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors(),
            )
        );
    }
}