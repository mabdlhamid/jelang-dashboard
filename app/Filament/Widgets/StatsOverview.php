<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Only visible to Owner
    
     protected static bool $isLazy = true;           // 👈 ADD
    protected static ?string $pollingInterval = null; // 👈 ADD

    public static function canView(): bool           // ← Already exists, keep it
    {
        return auth()->user()->isOwner();
    }

    protected function getStats(): array
    {
        // Calculate KPIs
        $totalRevenue = Sale::where('payment_status', 'paid')->sum('total_price');
        $totalTransactions = Sale::where('payment_status', 'paid')->count();
        $totalItemsSold = Sale::where('payment_status', 'paid')->sum('quantity');

        // Calculate monthly comparison
        $currentMonthRevenue = Sale::where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('total_price');

        $lastMonthRevenue = Sale::where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->sum('total_price');

        // Calculate percentage change
        $revenueChange = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description($revenueChange >= 0 ? 'Increased by ' . number_format(abs($revenueChange), 1) . '%' : 'Decreased by ' . number_format(abs($revenueChange), 1) . '%')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Total Transactions', number_format($totalTransactions))
                ->description('All paid transactions')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info')
                ->chart([3, 5, 3, 7, 4, 5, 6, 7]),

            Stat::make('Total Items Sold', number_format($totalItemsSold))
                ->description('Total quantity sold')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning')
                ->chart([5, 6, 3, 7, 3, 4, 5, 6]),
        ];
    }

    protected static ?int $sort = 1;
}