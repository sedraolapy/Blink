<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionFlexGroupsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_REPORTS->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'region' => [
                'required',
                'string',
                Rule::in(['damascus', 'other']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'region.required' => __('validation.required'),
            'region.string' => __('validation.string'),
            'region.in' => __('validation.in'),
        ];
    }

    public function attributes(): array
    {
        return [
            'region' => __('validation.attributes.region'),
        ];
    }
}
