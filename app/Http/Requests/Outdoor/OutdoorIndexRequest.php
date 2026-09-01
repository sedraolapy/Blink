<?php

namespace App\Http\Requests\Outdoor;

use App\Enums\AssetAvailabilityStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OutdoorIndexRequest extends FormRequest
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
                Rule::enum(AssetAvailabilityStatusEnum::class),
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
            'search.string' =>
                __('validation.outdoor.search.string'),

            'search.max' =>
                __('validation.outdoor.search.max'),

            'status.enum' =>
                __('validation.outdoor.status.enum'),

            'governorate_id.integer' =>
                __('validation.outdoor.governorate_id.integer'),

            'governorate_id.exists' =>
                __('validation.outdoor.governorate_id.exists'),

            'page.integer' =>
                __('validation.outdoor.page.integer'),

            'page.min' =>
                __('validation.outdoor.page.min'),
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => __('validation.attributes.search'),
            'status' => __('validation.attributes.status'),
            'governorate_id' => __('validation.attributes.governorate_id'),
            'page' => __('validation.attributes.page'),
        ];
    }

}
