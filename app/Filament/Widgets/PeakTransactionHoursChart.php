<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeakTransactionHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Peak Transaction Hours';

    protected static string $color = 'warning';

    protected static ?int $sort = 4;

    protected static bool $isLazy = true;           // 👈 ADD
    protected static ?string $pollingInterval = null; // 👈 ADD

    public static function canView(): bool           // 👈 ADD
    {
        return auth()->user()->isOwner();
    }

    protected function getData(): array
    {
        // Get transaction count by hour
        $hourlyData = Sale::select(
                DB::raw('HOUR(transaction_date) as hour'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->where('payment_status', 'paid')
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Prepare all 24 hours
        $labels = [];
        $data = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            
            $hourData = $hourlyData->first(function ($item) use ($hour) {
                return $item->hour == $hour;
            });
            
            $data[] = $hourData ? $hourData->transaction_count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $data,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.7)',
                    'borderColor' => 'rgba(251, 191, 36, 1)',
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
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
        ];
    }
}