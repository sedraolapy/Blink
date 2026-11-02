<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Filament\Facades\Filament;

class UserForm
{
    private static function isCurrentUserSuperAdmin(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            && $user->hasRole(RoleEnum::SUPER_ADMIN->value);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('name_translations')
                ->tabs([
                    Tab::make(__('filament/admin/user_resource.arabic'))
                        ->schema([
                            TextInput::make('name.ar')
                                ->label(__('filament/admin/user_resource.name.ar'))
                                ->required()
                                ->maxLength(255),
                        ]),

                    Tab::make(__('filament/admin/user_resource.english'))
                        ->schema([
                            TextInput::make('name.en')
                                ->label(__('filament/admin/user_resource.name.en'))
                                ->required()
                                ->maxLength(255),
                        ]),
                ])
                ->columnSpanFull(),

                TextInput::make('email')
                    ->label(__('filament/admin/user_resource.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label(__('filament/admin/user_resource.password'))
                    ->password()
                    ->revealable()
                    ->required(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    )
                    ->dehydrated(
                        fn (?string $state): bool =>
                            filled($state)
                    )
                    ->maxLength(255),

                Select::make('roles')
                    ->label(__('filament/admin/user_resource.roles'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder =>
                            self::isCurrentUserSuperAdmin()
                                ? $query
                                : $query->where(
                                    'name',
                                    '!=',
                                    RoleEnum::SUPER_ADMIN->value
                                )
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(
                        fn (Role $record): string =>
                            RoleEnum::tryFrom($record->name)?->label()
                            ?? $record->name
                    )
                    ->rule(
                        fn () => function (
                            string $attribute,
                            mixed $value,
                            Closure $fail
                        ): void {
                            if (self::isCurrentUserSuperAdmin()) {
                                return;
                            }

                            $hasSuperAdminRole = Role::query()
                                ->where('name', RoleEnum::SUPER_ADMIN->value)
                                ->whereIn('id', (array) $value)
                                ->exists();

                            if ($hasSuperAdminRole) {
                                $fail(__('validation.in', [
                                    'attribute' => __('filament/admin/user_resource.roles'),
                                ]));
                            }
                        }
                    )
                    ->required(),
            ]);
    }
}