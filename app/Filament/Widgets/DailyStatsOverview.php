<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\DailyClosing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->isAdmin();
    }

    protected static ?int $sort = 1;

    // 👇 THESE ARE CRITICAL
    protected static ?string $pollingInterval = null; // Disable auto-polling
    protected static bool $isLazy = false; // Load immediately

    protected function getStats(): array
    {
        // Get fresh data every time (no caching)
        $summary = Sale::getTodaySummary();
    $isClosed = DailyClosing::isCurrentlyClosed(); // 👈 CHANGED THIS

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($summary['total_revenue'], 0, ',', '.'))
                ->description($isClosed ? '🔒 Kas Ditutup' : '✅ Aktif')
                ->descriptionIcon($isClosed ? 'heroicon-m-lock-closed' : 'heroicon-m-check-circle')
                ->color($isClosed ? 'danger' : 'success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Transaksi Hari Ini', number_format($summary['total_transactions']))
                ->description('Total penjualan hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info')
                ->chart([3, 5, 3, 7, 4, 5, 6]),

            Stat::make('Barang Terjual Hari Ini', number_format($summary['total_items']))
                ->description('Total kuantitas')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning')
                ->chart([5, 6, 3, 7, 3, 4, 5]),
        ];
    }
}