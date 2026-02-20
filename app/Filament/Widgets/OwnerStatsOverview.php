<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class OwnerStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->isOwner();
    }

    protected static ?int $sort = 1;
    protected static bool $isLazy = true;

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

    protected function getStats(): array
    {
        $query = Sale::where('payment_status', 'paid');

        // Apply filter based on type
        match ($this->filterType) {
            'date' => $query->whereDate('transaction_date', $this->filterDate),
            'month' => $query->whereMonth('transaction_date', $this->filterMonth)
                            ->whereYear('transaction_date', $this->filterYear),
            'year' => $query->whereYear('transaction_date', $this->filterYear),
            default => $query->whereDate('transaction_date', now()),
        };

        $totalRevenue = $query->sum('total_price');
        $totalTransactions = $query->count();
        $totalItems = $query->sum('quantity');

        $label = $this->getFilterLabel();

          return [
        Stat::make("💰 Pendapatan {$label}", 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
            ->description('Total pendapatan periode terpilih')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart([12, 18, 15, 22, 20, 25, 23, 28, 26, 30, 29, 35])
            ->extraAttributes([
                'class' => 'stat-card-success',
            ]),

        Stat::make("🛒 Transaksi {$label}", number_format($totalTransactions))
            ->description('Total transaksi berhasil')
            ->descriptionIcon('heroicon-m-shopping-cart')
            ->color('info')
            ->chart([8, 12, 10, 15, 13, 18, 16, 20, 18, 22, 21, 25])
            ->extraAttributes([
                'class' => 'stat-card-info',
            ]),

        Stat::make("📦 Barang Terjual {$label}", number_format($totalItems))
            ->description('Total kuantitas produk')
            ->descriptionIcon('heroicon-m-cube')
            ->color('warning')
            ->chart([15, 20, 18, 25, 22, 28, 26, 32, 30, 35, 33, 40])
            ->extraAttributes([
                'class' => 'stat-card-warning',
            ]),
    ];
    }

    private function getFilterLabel(): string
    {
        return match ($this->filterType) {
            'date' => \Carbon\Carbon::parse($this->filterDate)->locale('id')->isoFormat('D MMM Y'),
            'month' => \Carbon\Carbon::create($this->filterYear, $this->filterMonth)->locale('id')->isoFormat('MMMM Y'),
            'year' => $this->filterYear,
            default => 'Hari Ini',
        };
    }
}