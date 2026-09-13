<?php

namespace App\Http\Requests\Booking\ElectronicBooking;

use App\Enums\BookingTypeEnum;
use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreElectronicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::CREATE_SCREEN_BOOKING->value
        ) ?? false;
    }
    public function rules(): array
    {
        $designs = is_array($this->input('designs'))
            ? $this->input('designs')
            : [];

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
                Rule::enum(BookingTypeEnum::class),
            ],

            'designs' => [
                'required',
                'array',
                'min:1',
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

            'periods.*.start_date' => [
                'required',
                'date',
            ],

            'periods.*.end_date' => [
                'required',
                'date',
                'after_or_equal:periods.*.start_date',
            ],

            // screens
            'periods.*.screens' => [
                'nullable',
                'array',
            ],

            'periods.*.screens.*.screen_id' => [
                'required',
                'integer',
                'exists:led_screens,id',
            ],

            'periods.*.screens.*.is_gift' => [
                'required',
                'boolean',
            ],

            'periods.*.screens.*.slides' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.screens.*.slides.*.slide_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'periods.*.screens.*.slides.*.design_name' => [
                'required',
                'string',
                'max:255',
                Rule::in($designs),
            ],

            // networks
            'periods.*.networks' => [
                'nullable',
                'array',
            ],

            'periods.*.networks.*.network_id' => [
                'required',
                'integer',
                'exists:led_networks,id',
            ],

            'periods.*.networks.*.is_gift' => [
                'required',
                'boolean',
            ],

            'periods.*.networks.*.screens' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.networks.*.screens.*.screen_id' => [
                'required',
                'integer',
                'exists:led_screens,id',
            ],

            'periods.*.networks.*.screens.*.slides' => [
                'required',
                'array',
                'min:1',
            ],

            'periods.*.networks.*.screens.*.slides.*.slide_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'periods.*.networks.*.screens.*.slides.*.design_name' => [
                'required',
                'string',
                'max:255',
                Rule::in($designs),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'advertiser_type.required_without' =>
                __('validation.electronic_booking.advertiser_type.required_without'),

            'periods.required' =>
                __('validation.electronic_booking.periods.required'),

            'periods.min' =>
                __('validation.electronic_booking.periods.min'),

            'periods.*.end_date.after_or_equal' =>
                __('validation.electronic_booking.end_date.after_or_equal'),

            'periods.*.screens.*.slides.min' =>
                __('validation.electronic_booking.slides.min'),

            'periods.*.networks.*.screens.min' =>
                __('validation.electronic_booking.network_screens.min'),

            'periods.*.networks.*.screens.*.slides.min' =>
                __('validation.electronic_booking.slides.min'),

            'periods.*.screens.*.slides.required' =>
                __('validation.electronic_booking.slides.min'),

            'periods.*.networks.*.screens.*.slides.required' =>
                __('validation.electronic_booking.slides.min'),

            'periods.*.screens.*.slides.*.design_name.in' =>
                __('validation.electronic_booking.design_name.in'),

            'periods.*.networks.*.screens.*.slides.*.design_name.in' =>
                __('validation.electronic_booking.design_name.in'),

            'designs.min' =>
                __('validation.electronic_booking.designs.min'),

            'designs.required' =>
                __('validation.electronic_booking.designs.min'),

            'periods.*.screens.*.slides.*.design_name.required' =>
                __('validation.electronic_booking.design_name.required'),

            'periods.*.networks.*.screens.*.slides.*.design_name.required' =>
                __('validation.electronic_booking.design_name.required'),
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

            'periods.*.start_date' =>
                __('validation.attributes.start_date'),

            'periods.*.end_date' =>
                __('validation.attributes.end_date'),

            'periods.*.screens' =>
                __('validation.attributes.screens'),

            'periods.*.screens.*.screen_id' =>
                __('validation.attributes.screen'),

            'periods.*.screens.*.is_gift' =>
                __('validation.attributes.is_gift'),

            'periods.*.screens.*.slides' =>
                __('validation.attributes.slides'),

            'periods.*.screens.*.slides.*.slide_number' =>
                __('validation.attributes.slide_number'),

            'periods.*.screens.*.slides.*.design_name' =>
                __('validation.attributes.design_name'),

            'periods.*.networks' =>
                __('validation.attributes.networks'),

            'periods.*.networks.*.network_id' =>
                __('validation.attributes.network'),

            'periods.*.networks.*.is_gift' =>
                __('validation.attributes.is_gift'),

            'periods.*.networks.*.screens' =>
                __('validation.attributes.screens'),

            'periods.*.networks.*.screens.*.screen_id' =>
                __('validation.attributes.screen'),

            'periods.*.networks.*.screens.*.slides' =>
                __('validation.attributes.slides'),

            'periods.*.networks.*.screens.*.slides.*.slide_number' =>
                __('validation.attributes.slide_number'),

            'periods.*.networks.*.screens.*.slides.*.design_name' =>
                __('validation.attributes.design_name'),
        ];
    }
}