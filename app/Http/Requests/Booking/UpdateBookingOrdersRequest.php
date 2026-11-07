<?php

namespace App\Http\Requests\Booking;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateBookingOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_ORDERS->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'order' => [
                'required',
                'string',
                Rule::in([
                    'installation',
                    'extension',
                    'operation',
                ]),
            ],

            'checked' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'order.required' => __('validation.required'),
            'order.string' => __('validation.string'),
            'order.in' => __('validation.in'),

            'checked.required' => __('validation.required'),
            'checked.boolean' => __('validation.boolean'),
        ];
    }

    public function attributes(): array
    {
        return [
            'order' => __('validation.attributes.order'),
            'checked' => __('validation.attributes.checked'),
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
