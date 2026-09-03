<?php

namespace App\Http\Requests\Customer;

use App\Enums\ContractStatusEnum;
use App\Enums\SubscriptionTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contract_status' => [
                'nullable',
                Rule::enum(ContractStatusEnum::class),
            ],

            'subscription_type' => [
                'nullable',
                Rule::enum(SubscriptionTypeEnum::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => __('validation.customer.page.integer'),
            'page.min' => __('validation.customer.page.min'),

            'search.string' => __('validation.customer.search.string'),
            'search.max' => __('validation.customer.search.max'),

            'contract_status.enum' =>
                __('validation.customer.contract_status.enum'),

            'subscription_type.enum' =>
                __('validation.customer.subscription_type.enum'),
        ];
    }

    public function attributes(): array
    {
        return [
            'page' => __('validation.attributes.page'),
            'search' => __('validation.attributes.search'),
            'contract_status' => __('validation.attributes.contract_status'),
            'subscription_type' => __('validation.attributes.subscription_type'),
        ];
    }
}
