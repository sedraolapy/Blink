<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],

            'name.ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name->ar'),
            ],

            'name.en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name->en'),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required'),
            'name.array' => __('validation.array'),

            'name.ar.required' => __('validation.required'),
            'name.ar.string' => __('validation.string'),
            'name.ar.max' => __('validation.max.string'),

            'name.en.required' => __('validation.required'),
            'name.en.string' => __('validation.string'),
            'name.en.max' => __('validation.max.string'),

            'name.ar.unique' => __('validation.unique'),
            'name.en.unique' => __('validation.unique'),

            'phone.string' => __('validation.string'),
            'phone.max' => __('validation.max.string'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.customer_name'),
            'name.ar' => __('validation.attributes.customer_name_ar'),
            'name.en' => __('validation.attributes.customer_name_en'),
            'phone' => __('validation.attributes.phone'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            )
        );
    }
}