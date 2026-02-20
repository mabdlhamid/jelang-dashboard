<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PeakTransactionHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Peak Transaction Hours';
    protected static string $color = 'warning';
    protected static ?int $sort = 5;
    protected static bool $isLazy = true;

    public static function canView(): bool
    {
        return auth()->user()->isOwner();
    }

    // Filter properties
    public ?string $filterType = 'date';
    public ?string $filterDate = null;
    public ?int $filterMonth = null;
    public ?int $filterYear = null;

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    #[On('filter-updated')]
    public function updateFilter($data): void
    {
        $this->filterType = $data['type'];
        $this->filterDate = $data['date'];
        $this->filterMonth = $data['month'];
        $this->filterYear = $data['year'];
    }

    protected function getData(): array
    {
        $query = Sale::select(
                DB::raw('HOUR(transaction_date) as hour'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->where('payment_status', 'paid');

        // Apply filter
        match ($this->filterType) {
            'date' => $query->whereDate('transaction_date', $this->filterDate),
            'month' => $query->whereMonth('transaction_date', $this->filterMonth)
                            ->whereYear('transaction_date', $this->filterYear),
            'year' => $query->whereYear('transaction_date', $this->filterYear),
            default => $query->whereDate('transaction_date', now()),
        };

        $hourlyData = $query->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        $labels = [];
        $data = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $hourData = $hourlyData->first(fn ($item) => $item->hour == $hour);
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
}