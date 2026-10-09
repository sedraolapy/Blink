<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\ExternalAssetTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TopRequestedAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $externalTypes = array_map(
            fn (ExternalAssetTypeEnum $type) => $type->value,
            ExternalAssetTypeEnum::cases()
        );

        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    'flex',
                    'electronic',
                    ...$externalTypes,
                ]),
            ],
        ];
    }
}