<?php

namespace App\Http\Requests\Orders\Outdoor;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class OutdoorOrderCustomersRequest extends FormRequest
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
            'outdoor_type' => [
                'required',
                Rule::enum(ExternalAssetTypeEnum::class),
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
            'outdoor_type.required' =>
                __('validation.outdoor_orders.required'),

            'outdoor_type.enum' =>
                __('validation.outdoor_orders.invalid_type'),
        ];
    }

    public function attributes(): array
    {
        return [
            'outdoor_type' =>
                __('validation.attributes.outdoor_type'),
        ];
    }

}
