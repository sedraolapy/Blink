<?php

namespace App\Http\Requests\Reports;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;

class CampaignStartReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            PermissionEnum::MANAGE_REPORTS->value
        ) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
