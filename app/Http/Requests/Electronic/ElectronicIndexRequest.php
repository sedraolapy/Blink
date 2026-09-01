<?php

namespace App\Http\Requests\Electronic;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ElectronicIndexRequest extends FormRequest
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
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => __('validation.electronic.search.string'),
            'search.max' => __('validation.electronic.search.max'),

            'governorate_id.integer' => __('validation.electronic.governorate_id.integer'),
            'governorate_id.exists' => __('validation.electronic.governorate_id.exists'),

            'page.integer' => __('validation.electronic.page.integer'),
            'page.min' => __('validation.electronic.page.min'),
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => __('validation.attributes.search'),
            'governorate_id' => __('validation.attributes.governorate_id'),
            'page' => __('validation.attributes.page'),
        ];
    }
}