<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Trend';
    protected static string $color = 'info';
    protected static ?int $sort = 4;
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
        return match ($this->filterType) {
            'date' => $this->getHourlyData(),
            'month' => $this->getDailyDataForMonth(),
            'year' => $this->getMonthlyDataForYear(),
            default => $this->getHourlyData(),
        };
    }

    private function getHourlyData(): array
    {
        $labels = [];
        $data = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            
            $revenue = Sale::where('payment_status', 'paid')
                ->whereDate('transaction_date', $this->filterDate)
                ->whereRaw('HOUR(transaction_date) = ?', [$hour])
                ->sum('total_price');
                
            $data[] = $revenue;
        }

        return $this->buildDataset('Pendapatan per Jam', $labels, $data);
    }

    private function getDailyDataForMonth(): array
    {
        $labels = [];
        $data = [];
        
        $startDate = \Carbon\Carbon::create($this->filterYear, $this->filterMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $labels[] = $date->format('d M');
            
            $revenue = Sale::where('payment_status', 'paid')
                ->whereDate('transaction_date', $date->toDateString())
                ->sum('total_price');
                
            $data[] = $revenue;
        }

        return $this->buildDataset('Pendapatan per Hari', $labels, $data);
    }

    private function getMonthlyDataForYear(): array
    {
        $labels = [];
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = \Carbon\Carbon::create($this->filterYear, $month)->locale('id')->isoFormat('MMM');
            
            $revenue = Sale::where('payment_status', 'paid')
                ->whereYear('transaction_date', $this->filterYear)
                ->whereMonth('transaction_date', $month)
                ->sum('total_price');
                
            $data[] = $revenue;
        }

        return $this->buildDataset('Pendapatan per Bulan', $labels, $data);
    }

    private function buildDataset(string $label, array $labels, array $data): array
    {
        return [
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}