<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class UserResource extends Resource
{

    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return __('filament/admin/navigation.administration');
    }

    private static function isCurrentUserSuperAdmin(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User
            && $user->hasRole(RoleEnum::SUPER_ADMIN->value);
    }

    public static function getEditAuthorizationResponse(Model $record): Response
    {
        if (
            $record instanceof User &&
            $record->hasRole(RoleEnum::SUPER_ADMIN->value) &&
            ! static::isCurrentUserSuperAdmin()
        ) {
            return Response::deny();
        }

        return parent::getEditAuthorizationResponse($record);
    }

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        if (
            $record instanceof User &&
            $record->hasRole(RoleEnum::SUPER_ADMIN->value) &&
            ! static::isCurrentUserSuperAdmin()
        ) {
            return Response::deny();
        }

        return parent::getDeleteAuthorizationResponse($record);
    }

    public static function getDeleteAnyAuthorizationResponse(): Response
    {
        if (! static::isCurrentUserSuperAdmin()) {
            return Response::deny();
        }

        return parent::getDeleteAnyAuthorizationResponse();
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
        public static function getNavigationLabel(): string
    {
        return __('filament/admin/user_resource.navigation_label');
    }
        public static function getModelLabel(): string
    {
        return __('filament/admin/user_resource.model_label');
    }
        public static function getPluralModelLabel(): string
    {
        return __('filament/admin/user_resource.plural_model_label');
    }



}
