<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Menu;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class BusinessInsights extends Widget
{
    protected static string $view = 'filament.widgets.business-insights';
    
    protected static ?int $sort = 99; // Show at top
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->isOwner();
    }

    /**
     * Get all business insights
     */
    public function getInsights(): array
    {
        $insights = [];

        // 1. Monthly Revenue Comparison
        $revenueInsight = $this->getRevenueInsight();
        if ($revenueInsight) {
            $insights[] = $revenueInsight;
        }

        // 2. Best Selling Product Trend
        $productInsight = $this->getProductTrendInsight();
        if ($productInsight) {
            $insights[] = $productInsight;
        }

        // 3. Weekly Performance
        $weeklyInsight = $this->getWeeklyPerformanceInsight();
        if ($weeklyInsight) {
            $insights[] = $weeklyInsight;
        }

        // 4. Peak Hour Analysis
        $peakHourInsight = $this->getPeakHourInsight();
        if ($peakHourInsight) {
            $insights[] = $peakHourInsight;
        }

        // 5. Daily Pattern
        $dailyInsight = $this->getDailyPatternInsight();
        if ($dailyInsight) {
            $insights[] = $dailyInsight;
        }

        return $insights;
    }

    /**
     * 1. Monthly Revenue Comparison
     */
    private function getRevenueInsight(): ?array
    {
        $currentMonth = Sale::where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('total_price');

        $previousMonth = Sale::where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->sum('total_price');

        if ($previousMonth == 0) {
            return null;
        }

        $changePercent = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        if (abs($changePercent) < 1) {
            return null; // Skip insignificant changes
        }

        if ($changePercent > 0) {
            return [
                'type' => 'success',
                'icon' => '📈',
                'title' => 'Performa Penjualan Meningkat',
                'message' => sprintf(
                    'Penjualan bulan ini naik %.1f%% dibanding bulan lalu (Rp %s → Rp %s)',
                    abs($changePercent),
                    number_format($previousMonth, 0, ',', '.'),
                    number_format($currentMonth, 0, ',', '.')
                ),
                'recommendation' => 'Pertahankan strategi penjualan yang sedang berjalan.',
            ];
        } else {
            return [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Penjualan Menurun',
                'message' => sprintf(
                    'Penjualan bulan ini turun %.1f%% dibanding bulan lalu (Rp %s → Rp %s)',
                    abs($changePercent),
                    number_format($previousMonth, 0, ',', '.'),
                    number_format($currentMonth, 0, ',', '.')
                ),
                'recommendation' => 'Pertimbangkan strategi promosi atau evaluasi menu.',
            ];
        }
    }

    /**
     * 2. Product Trend Insight
     */
    private function getProductTrendInsight(): ?array
    {
        // Current month data
        $currentMonth = Sale::select('menu_id', DB::raw('SUM(quantity) as total'))
            ->where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->groupBy('menu_id')
            ->with('menu')
            ->get()
            ->keyBy('menu_id');

        // Previous month data
        $previousMonth = Sale::select('menu_id', DB::raw('SUM(quantity) as total'))
            ->where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->groupBy('menu_id')
            ->with('menu')
            ->get()
            ->keyBy('menu_id');

        $maxGrowth = 0;
        $trendingMenu = null;

        foreach ($currentMonth as $menuId => $current) {
            $previous = $previousMonth[$menuId]->total ?? 0;
            
            if ($previous > 0) {
                $growth = (($current->total - $previous) / $previous) * 100;
            } else {
                $growth = 100;
            }

            if ($growth > $maxGrowth && $growth > 20) {
                $maxGrowth = $growth;
                $trendingMenu = $current;
            }
        }

        if ($trendingMenu) {
            return [
                'type' => 'info',
                'icon' => '🔥',
                'title' => 'Menu Populer',
                'message' => sprintf(
                    '%s mengalami peningkatan permintaan %d%% bulan ini (Terjual: %d unit)',
                    $trendingMenu->menu->name ?? 'Menu',
                    round($maxGrowth),
                    $trendingMenu->total
                ),
                'recommendation' => 'Pastikan stok menu ini selalu tersedia.',
            ];
        }

        return null;
    }

    /**
     * 3. Weekly Performance
     */
    private function getWeeklyPerformanceInsight(): ?array
    {
        $thisWeek = Sale::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [now()->startOfWeek(), now()])
            ->sum('total_price');

        $lastWeek = Sale::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ])
            ->sum('total_price');

        if ($lastWeek == 0) {
            return null;
        }

        $weeklyChange = (($thisWeek - $lastWeek) / $lastWeek) * 100;

        if ($weeklyChange < -15) {
            return [
                'type' => 'danger',
                'icon' => '⚡',
                'title' => 'Peringatan: Penurunan Signifikan',
                'message' => sprintf(
                    'Penjualan minggu ini turun %.1f%% dibanding minggu lalu',
                    abs($weeklyChange)
                ),
                'recommendation' => 'Segera evaluasi: cek menu, harga, atau lakukan promosi khusus.',
            ];
        }

        return null;
    }

    /**
     * 4. Peak Hour Analysis
     */
    private function getPeakHourInsight(): ?array
    {
        $peakHour = Sale::select(
                DB::raw('HOUR(transaction_date) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->month)
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        if (!$peakHour || $peakHour->count < 5) {
            return null;
        }

        return [
            'type' => 'success',
            'icon' => '⏰',
            'title' => 'Jam Puncak Operasional',
            'message' => sprintf(
                'Pukul %02d:00 - %02d:00 adalah jam tersibuk dengan %d transaksi',
                $peakHour->hour,
                $peakHour->hour + 1,
                $peakHour->count
            ),
            'recommendation' => 'Pastikan staff dan stok memadai di jam ini.',
        ];
    }

    /**
     * 5. Daily Pattern Insight
     */
    private function getDailyPatternInsight(): ?array
    {
        $salesByDay = Sale::select(
                DB::raw('DAYOFWEEK(transaction_date) as day'),
                DB::raw('AVG(total_price) as avg')
            )
            ->where('payment_status', 'paid')
            ->whereMonth('transaction_date', now()->month)
            ->groupBy('day')
            ->orderBy('avg', 'desc')
            ->get();

        if ($salesByDay->isEmpty() || $salesByDay->count() < 2) {
            return null;
        }

        $best = $salesByDay->first();
        $worst = $salesByDay->last();

        $days = ['', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        $bestDay = $days[$best->day] ?? 'Unknown';
        $worstDay = $days[$worst->day] ?? 'Unknown';

        return [
            'type' => 'info',
            'icon' => '📅',
            'title' => 'Pola Penjualan Harian',
            'message' => sprintf(
                '%s adalah hari dengan performa terbaik (Rata-rata: Rp %s). %s cenderung lebih sepi.',
                $bestDay,
                number_format($best->avg, 0, ',', '.'),
                $worstDay
            ),
            'recommendation' => sprintf(
                'Pertimbangkan promosi khusus di hari %s untuk meningkatkan traffic.',
                $worstDay
            ),
        ];
    }

    // Helper methods for styling
    public function getGradient(string $type): string
    {
        return match($type) {
            'success' => 'from-green-400 to-green-600',
            'warning' => 'from-yellow-400 to-orange-600',
            'danger' => 'from-red-400 to-red-600',
            'info' => 'from-blue-400 to-blue-600',
            default => 'from-gray-400 to-gray-600',
        };
    }

    public function getBorderColor(string $type): string
    {
        return match($type) {
            'success' => 'border-green-200 dark:border-green-800',
            'warning' => 'border-yellow-200 dark:border-yellow-800',
            'danger' => 'border-red-200 dark:border-red-800',
            'info' => 'border-blue-200 dark:border-blue-800',
            default => 'border-gray-200 dark:border-gray-700',
        };
    }

    public function getBgColor(string $type): string
    {
        return match($type) {
            'success' => 'bg-green-100 dark:bg-green-900/30',
            'warning' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'danger' => 'bg-red-100 dark:bg-red-900/30',
            'info' => 'bg-blue-100 dark:bg-blue-900/30',
            default => 'bg-gray-100 dark:bg-gray-900/30',
        };
    }

    public function getBadgeColor(string $type): string
    {
        return match($type) {
            'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
            'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
        };
    }
}