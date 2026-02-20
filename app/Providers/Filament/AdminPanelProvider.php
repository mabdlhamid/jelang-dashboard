<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\BestSellingMenuChart;
use App\Filament\Widgets\MonthlySalesChart;
use App\Filament\Widgets\PeakTransactionHoursChart;
use App\Filament\Widgets\DailyStatsOverview;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\OwnerStatsOverview;
use App\Filament\Widgets\BusinessInsights;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id(id: 'admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->brandName('Jelang Koffie')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            // ✅ NO discoverWidgets - register manually only
            ->widgets([
    
                DailyStatsOverview::class,      // Admin only (canView checks role)
                OwnerStatsOverview::class,       // 👈 NEW - replaces old StatsOverview
                BestSellingMenuChart::class,    // Owner only (canView checks role)
                MonthlySalesChart::class,       // Owner only (canView checks role)
                PeakTransactionHoursChart::class, // Owner only (canView checks role)
                BusinessInsights::class,           // 👈 ADD at top

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
            ]);
    }
}
