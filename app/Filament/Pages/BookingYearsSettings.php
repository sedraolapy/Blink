<?php

namespace App\Filament\Pages;

use App\Models\WorkingYear;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class BookingYearsSettings extends Page
{
    protected string $view = 'filament.pages.booking-years-settings';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/booking_years_settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament/admin/booking_years_settings.title');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $currentYear = now()->year;

        WorkingYear::query()->firstOrCreate([
            'year' => $currentYear,
        ]);

        $years = WorkingYear::query()
            ->orderBy('year')
            ->pluck('year')
            ->map(
                fn ($year) => (string) $year
            )
            ->values()
            ->all();

        $this->form->fill([
            'years' => $years,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TagsInput::make('years')
                    ->label(
                        __('filament/admin/booking_years_settings.available_years')
                    )
                    ->placeholder('2027')
                    ->helperText(
                        __('filament/admin/booking_years_settings.helper_text')
                    )
                    ->required()
                    ->nestedRecursiveRules([
                        'integer',
                        'digits:4',
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $currentYear = now()->year;

        $years = collect(
            $data['years'] ?? []
        )
            ->map(
                fn ($year) => (int) $year
            )
            ->push($currentYear)
            ->unique()
            ->sort()
            ->values();

        DB::transaction(
            function () use ($years) {
                WorkingYear::query()
                    ->whereNotIn('year', $years)
                    ->delete();

                foreach ($years as $year) {
                    WorkingYear::query()->firstOrCreate([
                        'year' => $year,
                    ]);
                }
            }
        );

        $this->form->fill([
            'years' => $years
                ->map(
                    fn ($year) => (string) $year
                )
                ->all(),
        ]);

        Notification::make()
            ->title(
                __('filament/admin/booking_years_settings.saved')
            )
            ->success()
            ->send();
    }
}