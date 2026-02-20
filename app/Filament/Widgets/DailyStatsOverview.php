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
        Stat::make('💵 Pendapatan Hari Ini', 'Rp ' . number_format($summary['total_revenue'], 0, ',', '.'))
            ->description($isClosed ? '🔒 Kas sudah ditutup' : '✅ Kas sedang aktif')
            ->descriptionIcon($isClosed ? 'heroicon-m-lock-closed' : 'heroicon-m-check-circle')
            ->color($isClosed ? 'danger' : 'success')
            ->chart([8, 12, 10, 15, 13, 18, 16, 20])
            ->extraAttributes([
                'class' => $isClosed ? 'stat-card-danger' : 'stat-card-success',
            ]),

        Stat::make('🧾 Transaksi Hari Ini', number_format($summary['total_transactions']))
            ->description('Total penjualan hari ini')
            ->descriptionIcon('heroicon-m-receipt-percent')
            ->color('info')
            ->chart([5, 8, 6, 10, 8, 12, 11, 14])
            ->extraAttributes([
                'class' => 'stat-card-info',
            ]),

        Stat::make('📦 Barang Terjual Hari Ini', number_format($summary['total_items']))
            ->description('Total kuantitas hari ini')
            ->descriptionIcon('heroicon-m-shopping-bag')
            ->color('warning')
            ->chart([10, 15, 12, 18, 16, 22, 20, 25])
            ->extraAttributes([
                'class' => 'stat-card-warning',
            ]),
    ];

    }
}