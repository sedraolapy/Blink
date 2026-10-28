<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class CampaignRouteReportRequest extends FormRequest
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
            'governorate_ids' => [
                'sometimes',
                'array',
                'min:1',
            ],

            'governorate_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:governorates,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'governorate_ids.array' =>
                __('validation.array'),

            'governorate_ids.min' =>
                __('validation.min.array'),

            'governorate_ids.*.required' =>
                __('validation.required'),

            'governorate_ids.*.integer' =>
                __('validation.integer'),

            'governorate_ids.*.distinct' =>
                __('validation.distinct'),

            'governorate_ids.*.exists' =>
                __('validation.exists'),
        ];
    }

    public function attributes(): array
    {
        return [
            'governorate_ids' => __('validation.attributes.governorate_ids'),
            'governorate_ids.*' => __('validation.attributes.governorate_id'),
        ];
    }
}
