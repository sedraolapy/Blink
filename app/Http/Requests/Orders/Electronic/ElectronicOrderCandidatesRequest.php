<?php

namespace App\Http\Requests\Orders\Electronic;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ElectronicOrderCandidatesRequest extends FormRequest
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
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'period_id' => [
                'required',
                'integer',
                'exists:led_booking_periods,id',
            ],
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

    public function attributes(): array
    {
        return [
            'customer_id' =>
                __('validation.attributes.customer_id'),

            'period_id' =>
                __('validation.attributes.period_id'),
        ];
    }
}
