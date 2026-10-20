<?php

namespace App\Http\Requests\Orders\Outdoor;

use App\Enums\ExternalAssetTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class OutdoorOrderIssueRequest extends FormRequest
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
            'outdoor_type' => [
                'required',
                Rule::enum(ExternalAssetTypeEnum::class),
            ],

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'period_id' => [
                'required',
                'integer',
                'exists:external_booking_periods,id',
            ],

            'selected_asset_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'selected_asset_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:external_assets,id',
            ],

            'excluded_asset_ids' => [
                'present',
                'array',
            ],

            'excluded_asset_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:external_assets,id',
            ],

            'issue_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'recipient' => [
                'required',
                'string',
                'max:255',
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
            'outdoor_type.required' => __('validation.outdoor_orders.required'),
            'outdoor_type.enum' => __('validation.outdoor_orders.invalid_type'),

            'customer_id.required' => __('validation.outdoor_orders.required'),
            'customer_id.integer' => __('validation.outdoor_orders.integer'),
            'customer_id.exists' => __('validation.outdoor_orders.exists'),

            'period_id.required' => __('validation.outdoor_orders.required'),
            'period_id.integer' => __('validation.outdoor_orders.integer'),
            'period_id.exists' => __('validation.outdoor_orders.exists'),

            'selected_asset_ids.required' => __('validation.outdoor_orders.required'),
            'selected_asset_ids.array' => __('validation.outdoor_orders.array'),
            'selected_asset_ids.min' => __('validation.outdoor_orders.selected_min'),

            'selected_asset_ids.*.required' => __('validation.outdoor_orders.required'),
            'selected_asset_ids.*.integer' => __('validation.outdoor_orders.integer'),
            'selected_asset_ids.*.distinct' => __('validation.outdoor_orders.distinct'),
            'selected_asset_ids.*.exists' => __('validation.outdoor_orders.exists'),

            'excluded_asset_ids.present' => __('validation.outdoor_orders.present'),
            'excluded_asset_ids.array' => __('validation.outdoor_orders.array'),

            'excluded_asset_ids.*.required' => __('validation.outdoor_orders.required'),
            'excluded_asset_ids.*.integer' => __('validation.outdoor_orders.integer'),
            'excluded_asset_ids.*.distinct' => __('validation.outdoor_orders.distinct'),
            'excluded_asset_ids.*.exists' => __('validation.outdoor_orders.exists'),

            'issue_date.required' => __('validation.outdoor_orders.required'),
            'issue_date.date_format' => __('validation.outdoor_orders.date_format'),

            'recipient.required' => __('validation.outdoor_orders.required'),
            'recipient.string' => __('validation.outdoor_orders.string'),
            'recipient.max' => __('validation.outdoor_orders.max'),

            'execution_deadline.required' => __('validation.outdoor_orders.required'),
            'execution_deadline.date_format' => __('validation.outdoor_orders.date_format'),
            'execution_deadline.after_or_equal' => __('validation.outdoor_orders.after_or_equal'),

            'notes.string' => __('validation.outdoor_orders.string'),
        ];
    }


    public function attributes(): array
    {
        return [
            'outdoor_type' => __('validation.attributes.outdoor_type'),
            'customer_id' => __('validation.attributes.customer_id'),
            'period_id' => __('validation.attributes.outdoor_period_id'),

            'selected_asset_ids' => __('validation.attributes.selected_asset_ids'),
            'selected_asset_ids.*' => __('validation.attributes.selected_asset_id'),

            'excluded_asset_ids' => __('validation.attributes.excluded_asset_ids'),
            'excluded_asset_ids.*' => __('validation.attributes.excluded_asset_id'),

            'issue_date' => __('validation.attributes.issue_date'),
            'recipient' => __('validation.attributes.recipient'),
            'execution_deadline' => __('validation.attributes.execution_deadline'),
            'notes' => __('validation.attributes.notes'),
        ];
    }


}
