<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Menu;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::all();
        
        if ($menus->isEmpty()) {
            echo "⚠️  No menus found! Please run MenuSeeder first.\n";
            return;
        }

        $salesCount = 0;

        // Generate sales for the last 6 months
        $startDate = now()->subMonths(6);
        $endDate = now();

        // Generate 150 sales transactions
        for ($i = 0; $i < 150; $i++) {
            // Random date within last 6 months
            $transactionDate = Carbon::create(
                rand($startDate->year, $endDate->year),
                rand($startDate->month, $endDate->month),
                rand(1, 28),
                rand(7, 21), // Hours: 7 AM to 9 PM
                rand(0, 59),
                0
            );

            // Make sure date is within range
            if ($transactionDate->lt($startDate) || $transactionDate->gt($endDate)) {
                $transactionDate = $startDate->copy()->addDays(rand(0, 180));
            }

            // Select random menu
            $menu = $menus->random();
            
            // Random quantity (1-5 items)
            $quantity = rand(1, 5);
            
            // Calculate total price
            $totalPrice = $menu->price * $quantity;

            // 95% paid, 3% pending, 2% cancelled
            $rand = rand(1, 100);
            if ($rand <= 95) {
                $paymentStatus = 'paid';
            } elseif ($rand <= 98) {
                $paymentStatus = 'pending';
            } else {
                $paymentStatus = 'cancelled';
            }

            Sale::create([
                'menu_id' => $menu->id,
                'transaction_date' => $transactionDate,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'payment_status' => $paymentStatus,
            ]);

            $salesCount++;
        }

        // Generate more sales for current month (for better dashboard visibility)
        for ($i = 0; $i < 50; $i++) {
            $menu = $menus->random();
            $quantity = rand(1, 4);
            $totalPrice = $menu->price * $quantity;

            // Random time in current month
            $transactionDate = now()->subDays(rand(0, now()->day - 1))
                ->setHour(rand(7, 21))
                ->setMinute(rand(0, 59));

            Sale::create([
                'menu_id' => $menu->id,
                'transaction_date' => $transactionDate,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'payment_status' => 'paid',
            ]);

            $salesCount++;
        }

        echo "✅ {$salesCount} sales transactions created successfully!\n";
    }
}