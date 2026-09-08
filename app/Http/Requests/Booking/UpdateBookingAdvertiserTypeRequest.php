<?php

namespace App\Http\Requests\Booking;

use App\Enums\BookingTypeEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateBookingAdvertiserTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'advertiser_type' => [
                'required',
                Rule::enum(BookingTypeEnum::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'advertiser_type.required' =>
                __('validation.required'),

            'advertiser_type.enum' =>
                __('validation.enum'),
        ];
    }

    public function attributes(): array
    {
        return [
            'advertiser_type' =>
                __('validation.attributes.advertiser_type'),
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