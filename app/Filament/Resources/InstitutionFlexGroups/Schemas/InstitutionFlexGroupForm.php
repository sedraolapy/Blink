<?php

namespace App\Filament\Resources\InstitutionFlexGroups\Schemas;

use App\Models\InstitutionFlexGroup;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class InstitutionFlexGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('name_translations')
                    ->tabs([
                        Tab::make(
                            __('filament/admin/institution_flex_group_resource.arabic')
                        )
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label(
                                        __('filament/admin/institution_flex_group_resource.name_ar')
                                    )
                                    ->required()
                                    ->maxLength(255)
                                    ->rule(
                                        self::uniqueNameRule('ar')
                                    ),
                            ]),

                        Tab::make(
                            __('filament/admin/institution_flex_group_resource.english')
                        )
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(
                                        __('filament/admin/institution_flex_group_resource.name_en')
                                    )
                                    ->required()
                                    ->maxLength(255)
                                    ->rule(
                                        self::uniqueNameRule('en')
                                    ),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function uniqueNameRule(
        string $locale
    ): Closure {
        return function (?InstitutionFlexGroup $record) use ($locale): Closure {
            return function (
                string $attribute,
                mixed $value,
                Closure $fail
            ) use ($locale, $record): void {
                $query = InstitutionFlexGroup::query()
                    ->where(
                        "name->{$locale}",
                        trim((string) $value)
                    );

                if ($record !== null) {
                    $query->where(
                        'id',
                        '!=',
                        $record->getKey()
                    );
                }

                if ($query->exists()) {
                    $fail(
                        __('filament/admin/institution_flex_group_resource.duplicate_name')
                    );
                }
            };
        };
    }
}
