<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;

class InstitutionOutdoorExportRequest extends InstitutionOutdoorReportRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_REPORTS->value
        ) ?? false;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'from' => [
                'required',
                'date_format:Y-m-d',
            ],

            'to' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'from.required' => __('validation.required'),
            'from.date_format' => __('validation.date_format'),

            'to.required' => __('validation.required'),
            'to.date_format' => __('validation.date_format'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'month' => __('validation.attributes.month'),
            'from' => __('validation.attributes.from'),
            'to' => __('validation.attributes.to'),
        ];
    }
}
