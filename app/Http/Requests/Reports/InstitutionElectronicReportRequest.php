<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class InstitutionElectronicReportRequest extends FormRequest
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
            'month' => [
                'required',
                'integer',
                'between:1,12',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => __('validation.required'),
            'month.integer' => __('validation.integer'),
            'month.between' => __('validation.between.numeric'),
        ];
    }

    public function attributes(): array
    {
        return [
            'month' => __('validation.attributes.month'),
        ];
    }
}