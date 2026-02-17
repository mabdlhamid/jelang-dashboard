<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BestSellingMenuChart extends ChartWidget
{
    protected static ?string $heading = 'Best-Selling Menu';

    protected static string $color = 'success';

    protected static ?int $sort = 2;
     protected static bool $isLazy = true;           // 👈 ADD
    protected static ?string $pollingInterval = null; // 👈 ADD

    public static function canView(): bool           // 👈 ADD
    {
        return auth()->user()->isOwner();
    }

    protected function getData(): array
    {
        // Get top 10 best-selling menus
        $bestSelling = Sale::select('menu_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('payment_status', 'paid')
            ->groupBy('menu_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->with('menu')
            ->get();

        $labels = [];
        $data = [];

        foreach ($bestSelling as $sale) {
            $labels[] = $sale->menu->name ?? 'Unknown';
            $data[] = $sale->total_sold;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',   // green
                        'rgba(59, 130, 246, 0.7)',  // blue
                        'rgba(251, 191, 36, 0.7)',  // yellow
                        'rgba(239, 68, 68, 0.7)',   // red
                        'rgba(168, 85, 247, 0.7)',  // purple
                        'rgba(236, 72, 153, 0.7)',  // pink
                        'rgba(20, 184, 166, 0.7)',  // teal
                        'rgba(249, 115, 22, 0.7)',  // orange
                        'rgba(107, 114, 128, 0.7)', // gray
                        'rgba(14, 165, 233, 0.7)',  // sky
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(20, 184, 166, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(107, 114, 128, 1)',
                        'rgba(14, 165, 233, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}