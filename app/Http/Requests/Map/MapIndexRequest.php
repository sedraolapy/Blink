<?php

namespace App\Http\Requests\Map;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapIndexRequest extends FormRequest
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

            'type' => [
                'nullable',
                Rule::in([
                    'flex',
                    'electronic',
                    'mural',
                    'rooftop',
                    'tunnel',
                    'bridge',
                    'unipole',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' =>
                __('validation.map.search.string'),

            'search.max' =>
                __('validation.map.search.max'),

            'type.in' =>
                __('validation.map.type.in'),
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => __('validation.attributes.search'),
            'type' => __('validation.attributes.type'),
        ];
    }

}
