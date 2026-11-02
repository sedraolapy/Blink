<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNotificationRequest extends FormRequest
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
            'filter' => [
                'sometimes',
                Rule::in(['all', 'read', 'unread']),
            ],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'filter.in' =>
                __('validation.notifications.filter.in'),

            'page.integer' =>
                __('validation.notifications.page.integer'),

            'page.min' =>
                __('validation.notifications.page.min'),

            'per_page.integer' =>
                __('validation.notifications.per_page.integer'),

            'per_page.min' =>
                __('validation.notifications.per_page.min'),

            'per_page.max' =>
                __('validation.notifications.per_page.max'),
        ];
    }

    public function attributes(): array
    {
        return [
            'filter' =>
                __('validation.attributes.notification_filter'),

            'page' =>
                __('validation.attributes.page'),

            'per_page' =>
                __('validation.attributes.notification_per_page'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            sendError(
                __('messages.validation_failed'),
                422,
                $validator->errors()
            )
        );
    }
}
