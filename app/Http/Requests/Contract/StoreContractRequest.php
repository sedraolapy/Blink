<?php

namespace App\Http\Requests\Contract;

use App\Enums\PermissionEnum;
use App\Services\WorkingYear\WorkingYearContext;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::UPLOAD_CONTRACT->value
        ) ?? false;
    }

    public function rules(): array
    {
        $year = app(WorkingYearContext::class)->get();

        return [
            'contract_number' => [
                'required',
                'string',
                'max:255',
            ],

            'start_date' => [
                'required',
                'date_format:Y-m-d',
                "after_or_equal:{$year}-01-01",
                "before_or_equal:{$year}-12-31",
            ],

            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                "before_or_equal:{$year}-12-31",
            ],

            'contract_images' => [
                'required',
                'array',
                'min:1',
            ],

            'contract_images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],

            'attachment_images' => [
                'nullable',
                'array',
            ],

            'attachment_images.*' => [
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' =>
                __('validation.contract.start_date.within_working_year'),

            'start_date.before_or_equal' =>
                __('validation.contract.start_date.within_working_year'),

            'end_date.after_or_equal' =>
                __('validation.contract.end_date.after_or_equal'),

            'end_date.before_or_equal' =>
                __('validation.contract.end_date.within_working_year'),

            'contract_images.min' =>
                __('validation.contract.contract_images.min'),
        ];
    }

    public function attributes(): array
    {
        return [
            'contract_number' =>
                __('validation.attributes.contract_number'),

            'start_date' =>
                __('validation.attributes.start_date'),

            'end_date' =>
                __('validation.attributes.end_date'),

            'contract_images' =>
                __('validation.attributes.contract_images'),

            'contract_images.*' =>
                __('validation.attributes.contract_image'),

            'attachment_images' =>
                __('validation.attributes.attachment_images'),

            'attachment_images.*' =>
                __('validation.attributes.attachment_image'),
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