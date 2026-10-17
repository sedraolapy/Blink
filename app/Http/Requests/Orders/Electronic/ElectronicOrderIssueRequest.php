<?php

namespace App\Http\Requests\Orders\Electronic;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ElectronicOrderIssueRequest extends FormRequest
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
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'period_id' => [
                'required',
                'integer',
                'exists:led_booking_periods,id',
            ],

            'selected_screen_ids' => [
                'required_without:selected_network_ids',
                'array',
            ],

            'selected_screen_ids.*' => [
                'integer',
                'distinct',
                'exists:led_screens,id',
            ],

            'selected_network_ids' => [
                'required_without:selected_screen_ids',
                'array',
            ],

            'selected_network_ids.*' => [
                'integer',
                'distinct',
                'exists:led_networks,id',
            ],


            'issue_date' => [
                'required',
                'date',
            ],

            'recipient' => [
                'required',
                'string',
                'max:255',
            ],

            'execution_deadline' => [
                'required',
                'date',
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
}
