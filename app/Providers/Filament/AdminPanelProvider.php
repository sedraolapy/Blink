<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\View\PanelsRenderHook;
use Filament\Navigation\NavigationGroup;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('auth/admin/blink')
            ->login()
            ->brandLogo(asset('images/blink-logo.svg'))
            ->brandLogoHeight('5rem')
            ->favicon(asset('images/blink-tab.svg'))
            ->colors([
                'primary' => '#2F1660',
                'secondary' => '#8801FE',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string =>
                        __('filament/admin/navigation.advertising_assets')
                    ),

                NavigationGroup::make()
                    ->label(fn (): string =>
                        __('filament/admin/navigation.master_data')
                    ),

                NavigationGroup::make()
                    ->label(fn (): string =>
                        __('filament/admin/navigation.administration')
                    ),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentLanguageSwitcherPlugin::make()
                    ->locales([
                        [
                            'code' => 'en',
                            'name' => 'English',
                            'flag' => 'gb',
                        ],
                        [
                            'code' => 'ar',
                            'name' => 'العربية',
                            'flag' => 'sa',
                        ],
                    ])
                    ->showFlags(false)
                    ->renderHook(PanelsRenderHook::USER_MENU_BEFORE),
            ]);
    }
}
