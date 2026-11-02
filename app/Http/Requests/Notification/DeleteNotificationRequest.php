<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'notification_ids.*' => [
                'required',
                'uuid',
                'distinct',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'notification_ids.required' =>
                __('validation.notifications.delete.ids_required'),

            'notification_ids.array' =>
                __('validation.notifications.delete.ids_array'),

            'notification_ids.min' =>
                __('validation.notifications.delete.ids_min'),

            'notification_ids.max' =>
                __('validation.notifications.delete.ids_max'),

            'notification_ids.*.required' =>
                __('validation.notifications.delete.id_required'),

            'notification_ids.*.uuid' =>
                __('validation.notifications.delete.id_uuid'),

            'notification_ids.*.distinct' =>
                __('validation.notifications.delete.id_distinct'),
        ];
    }

    public function attributes(): array
    {
        return [
            'notification_ids' =>
                __('validation.attributes.notification_ids'),

            'notification_ids.*' =>
                __('validation.attributes.notification_id'),
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
