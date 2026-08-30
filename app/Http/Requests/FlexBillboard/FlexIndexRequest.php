<?php

namespace App\Http\Requests\FlexBillboard;

use App\Enums\FlexStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlexIndexRequest extends FormRequest
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

            'status' => [
                'nullable',
                Rule::enum(FlexStatusEnum::class),
            ],

            'governorate_id' => [
                'nullable',
                'integer',
                'exists:governorates,id',
            ],

            'period_id' => [
                'nullable',
                'integer',
                'exists:advertising_periods,id',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => __('validation.attributes.search'),
            'status' => __('validation.attributes.status'),
            'governorate_id' => __('validation.attributes.governorate_id'),
            'period_id' => __('validation.attributes.period_id'),
            'page' => __('validation.attributes.page'),
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => __('validation.string'),
            'search.max' => __('validation.max.string'),

            'status.enum' => __('validation.enum'),

            'governorate_id.integer' => __('validation.integer'),
            'governorate_id.exists' => __('validation.exists'),

            'period_id.integer' => __('validation.integer'),
            'period_id.exists' => __('validation.exists'),

            'page.integer' => __('validation.integer'),
            'page.min' => __('validation.min.numeric'),
        ];
    }
}
