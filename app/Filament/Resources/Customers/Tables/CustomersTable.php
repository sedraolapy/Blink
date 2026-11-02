<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\SubscriptionTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/admin/customer_resource.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('filament/admin/customer_resource.phone'))
                    ->searchable(),

                TextColumn::make('currentYearSubscription.subscription_type')
                    ->placeholder('-')
                    ->label(
                        __('filament/admin/customer_resource.subscription_type')
                    )
                    ->badge()
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state instanceof SubscriptionTypeEnum
                                ? $state->label()
                                : (
                                    SubscriptionTypeEnum::tryFrom($state)?->label()
                                    ?? $state
                                )
                    )
                    ->color('secondary'),

                TextColumn::make('created_at')
                    ->label(__('filament/admin/customer_resource.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament/admin/customer_resource.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subscription_type')
                    ->label(__('filament/admin/customer_resource.subscription_type'))
                    ->options(SubscriptionTypeEnum::options())
                    ->native(false)
                    ->query(
                        fn (Builder $query, array $data): Builder =>
                            $query->when(
                                $data['value'] ?? null,
                                fn (Builder $query, string $type): Builder =>
                                    $query->whereHas(
                                        'currentYearSubscription',
                                        fn (Builder $subscriptionQuery): Builder =>
                                            $subscriptionQuery->where(
                                                'subscription_type',
                                                $type
                                            )
                                    )
                            )
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading(
                        __('filament/admin/customer_resource.delete_customer_heading')
                    )
                    ->modalDescription(
                        __('filament/admin/customer_resource.delete_customer_description')
                    )
                    ->modalSubmitActionLabel(
                        __('filament/admin/customer_resource.delete_customer_submit')
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}