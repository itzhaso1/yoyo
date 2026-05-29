<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\PosTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin', 'role' => 'admin', 'password' => 'admin123']
        );

        User::updateOrCreate(
            ['username' => 'cashier'],
            ['name' => 'Cashier', 'role' => 'cashier', 'password' => 'cashier123']
        );

        $tables = [
            ['name' => 'T1', 'section' => 'indoor', 'seats' => 4, 'sort_order' => 1],
            ['name' => 'T2', 'section' => 'indoor', 'seats' => 4, 'sort_order' => 2],
            ['name' => 'T3', 'section' => 'indoor', 'seats' => 6, 'sort_order' => 3],
            ['name' => 'T4', 'section' => 'indoor', 'seats' => 2, 'sort_order' => 4],
            ['name' => 'O1', 'section' => 'outdoor', 'seats' => 4, 'sort_order' => 10],
            ['name' => 'O2', 'section' => 'outdoor', 'seats' => 4, 'sort_order' => 11],
            ['name' => 'O3', 'section' => 'outdoor', 'seats' => 6, 'sort_order' => 12],
            ['name' => 'VIP 1', 'section' => 'vip', 'seats' => 8, 'sort_order' => 20],
            ['name' => 'VIP 2', 'section' => 'vip', 'seats' => 8, 'sort_order' => 21],
        ];

        foreach ($tables as $table) {
            PosTable::updateOrCreate(['name' => $table['name']], $table + ['status' => 'free']);
        }

        $items = [
            ['name' => 'Double Apple Shisha', 'category' => 'shisha', 'price' => 12.00],
            ['name' => 'Mint Shisha', 'category' => 'shisha', 'price' => 10.00],
            ['name' => 'Turkish Coffee', 'category' => 'drink', 'price' => 4.00],
            ['name' => 'Latte', 'category' => 'drink', 'price' => 5.50],
            ['name' => 'Fresh Orange Juice', 'category' => 'drink', 'price' => 6.00],
            ['name' => 'Club Sandwich', 'category' => 'food', 'price' => 8.50],
            ['name' => 'Cheesecake', 'category' => 'food', 'price' => 5.00],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(['name' => $item['name']], $item + ['is_active' => true]);
        }
    }
}
