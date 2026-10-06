<?php

namespace App\Http\Requests\Quotation;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

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
            'final_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'final_amount.numeric' => __('validation.numeric'),
            'final_amount.min' => __('validation.min.numeric'),
        ];
    }

    public function attributes(): array
    {
        return [
            'final_amount' => __('validation.attributes.final_amount'),
        ];
    }
}