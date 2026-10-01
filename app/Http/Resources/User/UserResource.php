<?php

namespace App\Http\Resources\User;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class UserResource extends JsonResource
{
    public function __construct($resource, private readonly Collection $availableYears)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $currentYear = now()->year;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            'roles' => $this->getRoleNames()
                ->map(
                    fn (string $role) =>
                        RoleEnum::tryFrom($role)?->label() ?? $role
                )
                ->values(),

            'permissions' => $this->getAllPermissions()
                ->pluck('name')
                ->values(),

            'available_years' => $this->availableYears
                ->map(
                    fn ($workingYear) => [
                        'year' => (int) $workingYear->year,
                        'is_current' =>
                            (int) $workingYear->year === $currentYear,
                    ]
                )
                ->values(),
        ];
    }
}