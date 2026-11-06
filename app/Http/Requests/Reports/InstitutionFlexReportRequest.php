<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionFlexReportRequest extends FormRequest
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
            'period_id' => [
                'required',
                'integer',
                Rule::exists('advertising_periods', 'id'),
            ],

            'region' => [
                'required',
                'string',
                Rule::in(['damascus', 'other']),
            ],

            'group_id' => [
                'required',
                'integer',
                'min:0',
                Rule::when(
                    (int) $this->input('group_id') > 0,
                    Rule::exists('institution_flex_groups', 'id')
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'period_id.required' => __('validation.required'),
            'period_id.integer' => __('validation.integer'),
            'period_id.exists' => __('validation.exists'),

            'region.required' => __('validation.required'),
            'region.string' => __('validation.string'),
            'region.in' => __('validation.in'),

            'group_id.required' => __('validation.required'),
            'group_id.integer' => __('validation.integer'),
            'group_id.min' => __('validation.min.numeric'),
            'group_id.exists' => __('validation.exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'period_id' => __('validation.attributes.period_id'),
            'region' => __('validation.attributes.region'),
            'group_id' => __('validation.attributes.group_id'),
        ];
    }
}
