<?php

namespace App\Http\Requests\Quotation;

use App\Enums\PermissionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IssueQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::EXPORT_QUOTATION->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'html' => [
                'required',
                'string',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ) {
                    if (strlen($value) > 5 * 1024 * 1024) {
                        $fail(__('validation.max.string', [
                            'attribute' => 'html',
                            'max' => '5 MB',
                        ]));
                    }
                },
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