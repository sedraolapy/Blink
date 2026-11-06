<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Validation\Rule;

class InstitutionFlexExportRequest extends InstitutionFlexReportRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_REPORTS->value
        ) ?? false;
    }

    public function rules(): array
    {
        $requiresLabels =
            $this->input('region') === 'damascus'
            && (int) $this->input('group_id') > 0;

        return array_merge(parent::rules(), [
            'from' => [
                'required',
                'date_format:Y-m-d',
            ],

            'to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:from',
            ],

            'booked_label' => [
                Rule::requiredIf($requiresLabels),
                Rule::prohibitedIf(! $requiresLabels),
                'string',
                'max:255',
            ],

            'remaining_label' => [
                Rule::requiredIf($requiresLabels),
                Rule::prohibitedIf(! $requiresLabels),
                'string',
                'max:255',
            ],
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'from' => __('validation.attributes.from'),
            'to' => __('validation.attributes.to'),
            'booked_label' => __('validation.attributes.booked_label'),
            'remaining_label' => __('validation.attributes.remaining_label'),
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'from.required' => __('validation.required'),
            'from.date_format' => __('validation.date_format'),

            'to.required' => __('validation.required'),
            'to.date_format' => __('validation.date_format'),
            'to.after_or_equal' => __('validation.after_or_equal'),

            'booked_label.required' => __('validation.required'),
            'booked_label.prohibited' => __('validation.prohibited'),
            'remaining_label.required' => __('validation.required'),
            'remaining_label.prohibited' => __('validation.prohibited'),
        ]);
    }
}
