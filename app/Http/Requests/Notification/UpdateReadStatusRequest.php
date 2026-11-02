<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateReadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $all = $this->boolean('all');

        return [
            'notification_ids' => [
                Rule::requiredIf(! $all),
                Rule::prohibitedIf($all),
                'array',
                'min:1',
                'max:100',
            ],

            'notification_ids.*' => [
                'required',
                'uuid',
                'distinct',
            ],

            'all' => [
                'sometimes',
                'boolean',
                'accepted',
            ],

            'is_read' => [
                'required',
                'boolean',
                ...($all ? ['accepted'] : []),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'notification_ids.required' =>
                __('validation.notifications.read_status.ids_required'),

            'notification_ids.prohibited' =>
                __('validation.notifications.read_status.ids_prohibited'),

            'notification_ids.array' =>
                __('validation.notifications.read_status.ids_array'),

            'notification_ids.min' =>
                __('validation.notifications.read_status.ids_min'),

            'notification_ids.max' =>
                __('validation.notifications.read_status.ids_max'),

            'notification_ids.*.required' =>
                __('validation.notifications.read_status.id_required'),

            'notification_ids.*.uuid' =>
                __('validation.notifications.read_status.id_uuid'),

            'notification_ids.*.distinct' =>
                __('validation.notifications.read_status.id_distinct'),

            'all.boolean' =>
                __('validation.notifications.read_status.all_boolean'),

            'all.accepted' =>
                __('validation.notifications.read_status.all_accepted'),

            'is_read.required' =>
                __('validation.notifications.read_status.is_read_required'),

            'is_read.boolean' =>
                __('validation.notifications.read_status.is_read_boolean'),

            'is_read.accepted' =>
                __('validation.notifications.read_status.is_read_accepted'),
        ];
    }

    public function attributes(): array
    {
        return [
            'notification_ids' =>
                __('validation.attributes.notification_ids'),

            'notification_ids.*' =>
                __('validation.attributes.notification_id'),

            'all' =>
                __('validation.attributes.notification_all'),

            'is_read' =>
                __('validation.attributes.is_read'),
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
