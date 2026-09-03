<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBookingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',

                Rule::when(
                    $this->filled('from_date'),
                    ['after_or_equal:from_date']
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' =>
                __('validation.customer_bookings.page.integer'),

            'page.min' =>
                __('validation.customer_bookings.page.min'),

            'from_date.date' =>
                __('validation.customer_bookings.from_date.date'),

            'to_date.date' =>
                __('validation.customer_bookings.to_date.date'),

            'to_date.after_or_equal' =>
                __('validation.customer_bookings.to_date.after_or_equal'),
        ];
    }

    public function attributes(): array
    {
        return [
            'page' => __('validation.attributes.page'),
            'from_date' => __('validation.attributes.from_date'),
            'to_date' => __('validation.attributes.to_date'),
        ];
    }
}
