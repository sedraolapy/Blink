<?php

namespace App\Http\Requests\Reports;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionOutdoorReportRequest extends FormRequest
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
            'outdoor_type' => [
                'required',
                Rule::enum(ExternalAssetTypeEnum::class),
            ],

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
            'outdoor_type.required' => __('validation.required'),
            'outdoor_type.enum' => __('validation.in'),
            'month.required' => __('validation.required'),
            'month.integer' => __('validation.integer'),
            'month.between' => __('validation.between.numeric'),
        ];
    }
}
