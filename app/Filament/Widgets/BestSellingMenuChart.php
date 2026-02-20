<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class BestSellingMenuChart extends ChartWidget
{
    protected static ?string $heading = 'Best-Selling Menu';
    protected static string $color = 'success';
    protected static ?int $sort = 3;
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
        $query = Sale::select('menu_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('payment_status', 'paid');

        // Apply filter
        match ($this->filterType) {
            'date' => $query->whereDate('transaction_date', $this->filterDate),
            'month' => $query->whereMonth('transaction_date', $this->filterMonth)
                            ->whereYear('transaction_date', $this->filterYear),
            'year' => $query->whereYear('transaction_date', $this->filterYear),
            default => $query->whereDate('transaction_date', now()),
        };

        $bestSelling = $query->groupBy('menu_id')
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
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(20, 184, 166, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(107, 114, 128, 0.7)',
                        'rgba(14, 165, 233, 0.7)',
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
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}