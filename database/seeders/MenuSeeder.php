<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Coffee
            ['name' => 'Espresso', 'price' => 18000, 'category' => 'Coffee'],
            ['name' => 'Americano', 'price' => 20000, 'category' => 'Coffee'],
            ['name' => 'Cappuccino', 'price' => 25000, 'category' => 'Coffee'],
            ['name' => 'Café Latte', 'price' => 28000, 'category' => 'Coffee'],
            ['name' => 'Caramel Macchiato', 'price' => 32000, 'category' => 'Coffee'],
            ['name' => 'Mocha', 'price' => 30000, 'category' => 'Coffee'],
            
            // Non-Coffee
            ['name' => 'Hot Chocolate', 'price' => 25000, 'category' => 'Non-Coffee'],
            ['name' => 'Matcha Latte', 'price' => 28000, 'category' => 'Non-Coffee'],
            ['name' => 'Vanilla Milkshake', 'price' => 30000, 'category' => 'Non-Coffee'],
            
            // Tea
            ['name' => 'Green Tea', 'price' => 15000, 'category' => 'Tea'],
            ['name' => 'Lemon Tea', 'price' => 18000, 'category' => 'Tea'],
            
            // Food
            ['name' => 'Croissant', 'price' => 22000, 'category' => 'Food'],
            ['name' => 'Sandwich', 'price' => 35000, 'category' => 'Food'],
            
            // Snack
            ['name' => 'Chocolate Chip Cookie', 'price' => 15000, 'category' => 'Snack'],
            ['name' => 'Brownie', 'price' => 20000, 'category' => 'Dessert'],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }

        echo "✅ " . count($menus) . " menu items created successfully!\n";
    }
}