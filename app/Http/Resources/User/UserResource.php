<?php

namespace App\Http\Resources\User;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
        ];
    }
}
