<?php

namespace App\Http\Requests\Booking\FlexBooking;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreFlexBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
            ],

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'advertiser_type' => [
                'nullable',
                'required_without:booking_id',
                Rule::in([
                    'local',
                    'foreign',
                ]),
            ],

            'designs' => [
                'present',
                'array',
            ],

            'designs.*' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'periods' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.period_id' => [
                'required',
                'integer',
                'distinct',
                'exists:advertising_periods,id',
            ],

            'periods.*.items' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.items.*.flex_id' => [
                'required',
                'integer',
                'exists:flex_billboards,id',
            ],

            'periods.*.items.*.is_gift' => [
                'required',
                'boolean',
            ],

            'periods.*.items.*.has_dykes' => [
                'required',
                'boolean',
            ],

            'periods.*.items.*.design_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(
                    $this->input('designs', [])
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.integer' =>
                __('validation.integer'),

            'booking_id.exists' =>
                __('validation.exists'),

            'customer_id.required' =>
                __('validation.required'),

            'customer_id.integer' =>
                __('validation.integer'),

            'customer_id.exists' =>
                __('validation.exists'),

            'advertiser_type.required_without' =>
                __('validation.required'),

            'advertiser_type.in' =>
                __('validation.in'),

            'designs.present' =>
                __('validation.present'),

            'designs.array' =>
                __('validation.array'),

            'designs.*.required' =>
                __('validation.required'),

            'designs.*.string' =>
                __('validation.string'),

            'designs.*.max' =>
                __('validation.max.string'),

            'designs.*.distinct' =>
                __('validation.distinct'),

            'periods.required' =>
                __('validation.required'),

            'periods.array' =>
                __('validation.array'),

            'periods.min' =>
                __('validation.min.array'),

            'periods.*.period_id.required' =>
                __('validation.required'),

            'periods.*.period_id.integer' =>
                __('validation.integer'),

            'periods.*.period_id.distinct' =>
                __('validation.distinct'),

            'periods.*.period_id.exists' =>
                __('validation.exists'),

            'periods.*.items.required' =>
                __('validation.required'),

            'periods.*.items.array' =>
                __('validation.array'),

            'periods.*.items.min' =>
                __('validation.min.array'),

            'periods.*.items.*.flex_id.required' =>
                __('validation.required'),

            'periods.*.items.*.flex_id.integer' =>
                __('validation.integer'),

            'periods.*.items.*.flex_id.exists' =>
                __('validation.exists'),

            'periods.*.items.*.is_gift.required' =>
                __('validation.required'),

            'periods.*.items.*.is_gift.boolean' =>
                __('validation.boolean'),

            'periods.*.items.*.has_dykes.required' =>
                __('validation.required'),

            'periods.*.items.*.has_dykes.boolean' =>
                __('validation.boolean'),

            'periods.*.items.*.design_name.string' =>
                __('validation.string'),

            'periods.*.items.*.design_name.max' =>
                __('validation.max.string'),

            'periods.*.items.*.design_name.in' =>
                __('validation.in'),
        ];
    }

    public function attributes(): array
    {
        return [
            'booking_id' =>
                __('validation.attributes.booking_id'),

            'customer_id' =>
                __('validation.attributes.customer_id'),

            'advertiser_type' =>
                __('validation.attributes.advertiser_type'),

            'designs' =>
                __('validation.attributes.designs'),

            'designs.*' =>
                __('validation.attributes.design_name'),

            'periods' =>
                __('validation.attributes.periods'),

            'periods.*.period_id' =>
                __('validation.attributes.period_id'),

            'periods.*.items' =>
                __('validation.attributes.items'),

            'periods.*.items.*.flex_id' =>
                __('validation.attributes.flex_id'),

            'periods.*.items.*.is_gift' =>
                __('validation.attributes.is_gift'),

            'periods.*.items.*.has_dykes' =>
                __('validation.attributes.has_dykes'),

            'periods.*.items.*.design_name' =>
                __('validation.attributes.design_name'),
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors(),
            )
        );
    }
}