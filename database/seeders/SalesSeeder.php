<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        Sale::truncate();

        $menus = Menu::all();

        if ($menus->isEmpty()) {
            echo "Menu kosong! Jalankan seed/menu dulu.\n";
            return;
        }

        $hasTransactionCode = Schema::hasColumn('sales', 'transaction_code');

        $coffeeMenus = $menus->where('category', 'Coffee');
        $foodMenus = $menus->whereIn('category', ['Makanan', 'Rice Bowl', 'Snack']);
        $nonCoffeeMenus = $menus->whereIn('category', ['Non Coffee', 'Fresh', 'Manual Brew']);

        $startDate = Carbon::create(2025, 12, 1);
        $endDate = Carbon::create(2026, 3, 17);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $targetTransactions = 550;
        $totalTransactions = 0;
        $totalRows = 0;

        echo "Generating sales data...\n";
        echo "Period: " . $startDate->format('d M Y') . " - " . $endDate->format('d M Y') . "\n";
        echo "Multi item per transaction: ON\n\n";

        $weightedRandom = function (array $weights) {
            $roll = rand(1, array_sum($weights));
            $current = 0;

            foreach ($weights as $value => $weight) {
                $current += $weight;

                if ($roll <= $current) {
                    return (int) $value;
                }
            }

            return (int) array_key_first($weights);
        };

        $pickMenuByShift = function ($shift) use ($menus, $coffeeMenus, $foodMenus, $nonCoffeeMenus) {
            $menuRandom = rand(1, 100);

            if ($shift === 'pagi') {
                if ($menuRandom <= 70 && $coffeeMenus->isNotEmpty()) {
                    return $coffeeMenus->random();
                }

                if ($menuRandom <= 90 && $nonCoffeeMenus->isNotEmpty()) {
                    return $nonCoffeeMenus->random();
                }

                return $menus->random();
            }

            if ($shift === 'siang') {
                if ($menuRandom <= 45 && $foodMenus->isNotEmpty()) {
                    return $foodMenus->random();
                }

                if ($menuRandom <= 75 && $coffeeMenus->isNotEmpty()) {
                    return $coffeeMenus->random();
                }

                if ($nonCoffeeMenus->isNotEmpty()) {
                    return $nonCoffeeMenus->random();
                }

                return $menus->random();
            }

            if ($menuRandom <= 45 && $coffeeMenus->isNotEmpty()) {
                return $coffeeMenus->random();
            }

            if ($menuRandom <= 75) {
                $snacks = $foodMenus->where('category', 'Snack');

                if ($snacks->isNotEmpty()) {
                    return $snacks->random();
                }

                if ($foodMenus->isNotEmpty()) {
                    return $foodMenus->random();
                }
            }

            return $menus->random();
        };

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $isWeekend = $date->isWeekend();

            $dailyTransactions = $isWeekend ? rand(6, 8) : rand(3, 5);

            for ($i = 0; $i < $dailyTransactions; $i++) {
                $shiftRandom = rand(1, 100);

                if ($shiftRandom <= 20) {
                    $shift = 'pagi';
                    $hour = rand(7, 11);
                } elseif ($shiftRandom <= 50) {
                    $shift = 'siang';
                    $hour = rand(12, 18);
                } else {
                    $shift = 'malam';
                    $hour = rand(19, 22);
                }

                $minute = rand(0, 59);
                $time = $date->copy()->setTime($hour, $minute);

                $totalTransactions++;

                $transactionCode = 'TRX-' . $time->format('Ymd-His') . '-' . str_pad($totalTransactions, 5, '0', STR_PAD_LEFT);

                $itemsPerTransaction = $weightedRandom([
                    1 => 15,
                    2 => 40,
                    3 => 25,
                    4 => 15,
                    5 => 5,
                ]);

                $usedMenuIds = [];

                for ($j = 0; $j < $itemsPerTransaction; $j++) {
                    $menu = $pickMenuByShift($shift);

                    $attempt = 0;

                    while (in_array($menu->id, $usedMenuIds) && $attempt < 10) {
                        $menu = $pickMenuByShift($shift);
                        $attempt++;
                    }

                    if (in_array($menu->id, $usedMenuIds)) {
                        continue;
                    }

                    $usedMenuIds[] = $menu->id;

                    $qty = $weightedRandom([
                        1 => 30,
                        2 => 40,
                        3 => 18,
                        4 => 8,
                        5 => 4,
                    ]);

                    $data = [
                        'menu_id' => $menu->id,
                        'quantity' => $qty,
                        'total_price' => $menu->price * $qty,
                        'payment_status' => 'paid',
                        'transaction_date' => $time,
                    ];

                    if ($hasTransactionCode) {
                        $data['transaction_code'] = $transactionCode;
                    }

                    Sale::forceCreate($data);
                    $totalRows++;
                }

                if ($totalTransactions >= $targetTransactions) {
                    break 2;
                }
            }

            if ($date->day % 15 == 0) {
                echo $date->format('d M Y') . " - {$totalTransactions} transaksi, {$totalRows} baris item\n";
            }
        }

        echo "\nMemastikan semua menu terjual...\n";

        $soldIds = Sale::pluck('menu_id')->unique();
        $unsold = $menus->whereNotIn('id', $soldIds);

        foreach ($unsold as $menu) {
            $totalTransactions++;

            $time = Carbon::create(2025, 12, rand(1, 31))->setTime(rand(19, 22), rand(0, 59));
            $transactionCode = 'TRX-' . $time->format('Ymd-His') . '-' . str_pad($totalTransactions, 5, '0', STR_PAD_LEFT);

            $qty = rand(1, 4);

            $data = [
                'menu_id' => $menu->id,
                'quantity' => $qty,
                'total_price' => $menu->price * $qty,
                'payment_status' => 'paid',
                'transaction_date' => $time,
            ];

            if ($hasTransactionCode) {
                $data['transaction_code'] = $transactionCode;
            }

            Sale::forceCreate($data);
            $totalRows++;
        }

        $totalRevenue = Sale::sum('total_price');
        $totalItems = Sale::sum('quantity');
        $totalSaleRows = Sale::count();

        if ($hasTransactionCode) {
            $realTransactions = Sale::whereNotNull('transaction_code')
                ->distinct()
                ->count('transaction_code');
        } else {
            $realTransactions = $totalTransactions;
        }

        $avgTransPerDay = $totalDays > 0 ? round($realTransactions / $totalDays, 1) : 0;
        $avgItemPerTransaction = $realTransactions > 0 ? round($totalItems / $realTransactions, 1) : 0;

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "SELESAI!\n";
        echo str_repeat('=', 60) . "\n";
        echo "Total Transaksi:       {$realTransactions}\n";
        echo "Total Baris Item:      {$totalSaleRows}\n";
        echo "Total Items Sold:      {$totalItems}\n";
        echo "Total Revenue:         Rp " . number_format($totalRevenue, 0, ',', '.') . "\n";
        echo "Period:                " . $startDate->format('d M Y') . " - " . $endDate->format('d M Y') . "\n";
        echo "Days:                  {$totalDays} days\n";
        echo "Avg Trans/Day:         {$avgTransPerDay}\n";
        echo "Avg Item/Transaksi:    {$avgItemPerTransaction} unit\n";
        echo str_repeat('=', 60) . "\n";

        $countShift = function ($startHour, $endHour) use ($hasTransactionCode) {
            $query = Sale::whereRaw('HOUR(transaction_date) BETWEEN ? AND ?', [$startHour, $endHour]);

            if ($hasTransactionCode) {
                return $query->whereNotNull('transaction_code')
                    ->distinct()
                    ->count('transaction_code');
            }

            return $query->count();
        };

        $pagi = $countShift(7, 11);
        $siang = $countShift(12, 18);
        $malam = $countShift(19, 23);

        echo "\nBREAKDOWN PER SHIFT:\n";
        echo "Pagi  (07-11): {$pagi}\n";
        echo "Siang (12-18): {$siang}\n";
        echo "Malam (19-23): {$malam}\n";

        $top = Sale::join('menus', 'sales.menu_id', '=', 'menus.id')
            ->select('menus.name', 'menus.category', DB::raw('SUM(sales.quantity) as total_sold'))
            ->groupBy('menus.id', 'menus.name', 'menus.category')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        echo "\nTOP 5 BEST SELLING:\n";

        foreach ($top as $i => $s) {
            echo ($i + 1) . ". {$s->name} ({$s->category}) - {$s->total_sold} unit\n";
        }

        $categories = Sale::join('menus', 'sales.menu_id', '=', 'menus.id')
            ->select('menus.category', DB::raw('SUM(sales.quantity) as total'))
            ->groupBy('menus.category')
            ->orderBy('total', 'desc')
            ->get();

        echo "\nPENJUALAN PER KATEGORI:\n";

        foreach ($categories as $cat) {
            $percent = $totalItems > 0 ? round(($cat->total / $totalItems) * 100, 1) : 0;
            echo "{$cat->category}: {$cat->total} unit ({$percent}%)\n";
        }

        echo "\nData berhasil dibuat dengan variasi multi-item dan multi-quantity!\n";

        if (!$hasTransactionCode) {
            echo "\nCatatan: tabel sales belum punya kolom transaction_code.\n";
            echo "Data tetap bervariasi, tetapi beberapa item belum bisa dikelompokkan sebagai 1 transaksi asli.\n";
        }
    }
}