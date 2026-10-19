<?php

namespace App\Http\Requests\Orders\Flex;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlexOrderCandidatesRequest extends FormRequest
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
            'period_id' => [
                'required',
                'integer',
                'exists:advertising_periods,id',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'installation',
                    'extension',
                ]),
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


    public function messages(): array
    {
        return [
            'period_id.required' =>
                __('validation.custom.period_id.required'),

            'period_id.integer' =>
                __('validation.custom.period_id.integer'),

            'period_id.exists' =>
                __('validation.custom.period_id.exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'period_id' =>
                __('validation.attributes.period_id'),

            'type' =>
                __('validation.attributes.flex_order_type'),
        ];
    }

}
