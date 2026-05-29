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
            ['name' => 'مدير النظام', 'email' => 'admin@example.com', 'role' => 'admin', 'password' => 'admin123']
        );

        User::updateOrCreate(
            ['username' => 'cashier'],
            ['name' => 'الكاشير', 'email' => 'cashier@example.com', 'role' => 'cashier', 'password' => 'cashier123']
        );

        $tables = [
            ['name' => 'طاولة 1', 'section' => 'indoor', 'seats' => 4, 'sort_order' => 1],
            ['name' => 'طاولة 2', 'section' => 'indoor', 'seats' => 4, 'sort_order' => 2],
            ['name' => 'طاولة 3', 'section' => 'indoor', 'seats' => 6, 'sort_order' => 3],
            ['name' => 'طاولة 4', 'section' => 'indoor', 'seats' => 2, 'sort_order' => 4],
            ['name' => 'خارجي 1', 'section' => 'outdoor', 'seats' => 4, 'sort_order' => 10],
            ['name' => 'خارجي 2', 'section' => 'outdoor', 'seats' => 4, 'sort_order' => 11],
            ['name' => 'خارجي 3', 'section' => 'outdoor', 'seats' => 6, 'sort_order' => 12],
            ['name' => 'VIP 1', 'section' => 'vip', 'seats' => 8, 'sort_order' => 20],
            ['name' => 'VIP 2', 'section' => 'vip', 'seats' => 8, 'sort_order' => 21],
        ];

        foreach ($tables as $table) {
            PosTable::updateOrCreate(['name' => $table['name']], $table + ['status' => 'free']);
        }

        $items = [
            ['name' => 'شيشة تفاحتين', 'category' => 'shisha', 'price' => 12.00],
            ['name' => 'شيشة نعناع', 'category' => 'shisha', 'price' => 10.00],
            ['name' => 'قهوة تركية', 'category' => 'drink', 'price' => 4.00],
            ['name' => 'لاتيه', 'category' => 'drink', 'price' => 5.50],
            ['name' => 'عصير برتقال طازج', 'category' => 'drink', 'price' => 6.00],
            ['name' => 'ساندويتش كلوب', 'category' => 'food', 'price' => 8.50],
            ['name' => 'تشيز كيك', 'category' => 'food', 'price' => 5.00],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(['name' => $item['name']], $item + ['is_active' => true]);
        }
    }
}
