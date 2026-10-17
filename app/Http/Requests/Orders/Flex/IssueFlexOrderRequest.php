<?php

namespace App\Http\Requests\Orders\Flex;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class IssueFlexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_ORDERS->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'period_id' => [
                'required',
                'integer',
                'exists:advertising_periods,id',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'installation',
                    'extension',
                ]),
            ],

            'selected_flex_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'selected_flex_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:flex_billboards,id',
            ],

            'excluded_flex_ids' => [
                'present',
                'array',
            ],

            'excluded_flex_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:flex_billboards,id',
            ],

            'issue_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'recipient' => [
                'required',
                'string',
            ],

            'execution_deadline' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:issue_date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            )
        );
    }


    public function messages(): array
    {
        return [
            'period_id.required' => __('validation.flex_orders.required'),
            'period_id.integer' => __('validation.flex_orders.integer'),
            'period_id.exists' => __('validation.flex_orders.exists'),

            'type.required' => __('validation.flex_orders.required'),
            'type.string' => __('validation.flex_orders.string'),
            'type.in' => __('validation.flex_orders.in'),

            'selected_flex_ids.required' => __('validation.flex_orders.required'),
            'selected_flex_ids.array' => __('validation.flex_orders.array'),
            'selected_flex_ids.min' => __('validation.flex_orders.selected_min'),

            'selected_flex_ids.*.required' => __('validation.flex_orders.required'),
            'selected_flex_ids.*.integer' => __('validation.flex_orders.integer'),
            'selected_flex_ids.*.distinct' => __('validation.flex_orders.distinct'),
            'selected_flex_ids.*.exists' => __('validation.flex_orders.exists'),

            'excluded_flex_ids.present' => __('validation.flex_orders.present'),
            'excluded_flex_ids.array' => __('validation.flex_orders.array'),

            'excluded_flex_ids.*.required' => __('validation.flex_orders.required'),
            'excluded_flex_ids.*.integer' => __('validation.flex_orders.integer'),
            'excluded_flex_ids.*.distinct' => __('validation.flex_orders.distinct'),
            'excluded_flex_ids.*.exists' => __('validation.flex_orders.exists'),

            'issue_date.required' => __('validation.flex_orders.required'),
            'issue_date.date_format' => __('validation.flex_orders.date_format'),

            'recipient.required' => __('validation.flex_orders.required'),
            'recipient.string' => __('validation.flex_orders.string'),

            'execution_deadline.required' => __('validation.flex_orders.required'),
            'execution_deadline.date_format' => __('validation.flex_orders.date_format'),
            'execution_deadline.after_or_equal' => __('validation.flex_orders.after_or_equal'),

            'notes.string' => __('validation.flex_orders.string'),
        ];
    }


    public function attributes(): array
    {
        return [
            'period_id' =>
                __('validation.attributes.period_id'),

            'type' =>
                __('validation.attributes.flex_order_type'),

            'selected_flex_ids' =>
                __('validation.attributes.selected_flex_ids'),

            'selected_flex_ids.*' =>
                __('validation.attributes.selected_flex_id'),

            'excluded_flex_ids' =>
                __('validation.attributes.excluded_flex_ids'),

            'excluded_flex_ids.*' =>
                __('validation.attributes.excluded_flex_id'),

            'issue_date' =>
                __('validation.attributes.issue_date'),

            'recipient' =>
                __('validation.attributes.recipient'),

            'execution_deadline' =>
                __('validation.attributes.execution_deadline'),

            'notes' =>
                __('validation.attributes.notes'),
        ];
    }


}
