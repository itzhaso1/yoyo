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
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'مدير النظام', 'email' => 'admin@example.com', 'role' => 'admin', 'password' => 'admin123']
        );
        $admin->update(['name' => 'مدير النظام', 'email' => 'admin@example.com', 'role' => 'admin']);

        $cashier = User::firstOrCreate(
            ['username' => 'cashier'],
            ['name' => 'الكاشير', 'email' => 'cashier@example.com', 'role' => 'cashier', 'password' => 'cashier123']
        );
        $cashier->update(['name' => 'الكاشير', 'email' => 'cashier@example.com', 'role' => 'cashier']);

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

            ['name' => 'كوكتيل - أفوكادو سنشل مع قشطة', 'category' => 'drink', 'price' => 4.50],
            ['name' => 'كوكتيل - أفوكادو عادي', 'category' => 'drink', 'price' => 2.75],
            ['name' => 'كوكتيل - سنشل', 'category' => 'drink', 'price' => 2.75],
            ['name' => 'كوكتيل - عادي', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - منجا + فراولة وموز', 'category' => 'drink', 'price' => 2.50],
            ['name' => 'كوكتيل - منجا', 'category' => 'drink', 'price' => 2.00],
            ['name' => 'كوكتيل - كيوي وفراولة', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - كيوي وليمون', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'كوكتيل - فراولة موز وحليب', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'كوكتيل - ليمون ونعناع', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - فراولة وحليب', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - موز وحليب', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - ليمون نعناع', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'كوكتيل - فخفخينا سبيشل', 'category' => 'drink', 'price' => 2.00],

            ['name' => 'سموذي - بطيخ', 'category' => 'drink', 'price' => 2.25],
            ['name' => 'سموذي - كيوكي', 'category' => 'drink', 'price' => 2.15],
            ['name' => 'سموذي - كيوي', 'category' => 'drink', 'price' => 2.15],
            ['name' => 'سموذي - منجا', 'category' => 'drink', 'price' => 2.25],
            ['name' => 'سموذي - ياسمين عروس', 'category' => 'drink', 'price' => 2.25],
            ['name' => 'سموذي - توت مشكل', 'category' => 'drink', 'price' => 2.25],
            ['name' => 'سموذي - منجا مع ياسمين عروس', 'category' => 'drink', 'price' => 2.25],

            ['name' => 'قهوة باردة - ايس كابتشينو', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'قهوة باردة - ايس موكا', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'قهوة باردة - ايس لاتيه', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'قهوة باردة - كراميل ميكاتو', 'category' => 'drink', 'price' => 1.75],

            ['name' => 'موهيتو طاقة - فراولة', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - كيوي', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - منجا', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - تلاجي', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - بلوكر سور', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - أناناس', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - خوخ', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - باشن', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - موكس فروت', 'category' => 'drink', 'price' => 1.25],
            ['name' => 'موهيتو طاقة - ورد', 'category' => 'drink', 'price' => 1.75],
            ['name' => 'موهيتو طاقة - لوجو بلو', 'category' => 'drink', 'price' => 1.50],

            ['name' => 'ملك شيك - تويكس', 'category' => 'drink', 'price' => 2.00],
            ['name' => 'ملك شيك - أوريو', 'category' => 'drink', 'price' => 2.00],
            ['name' => 'ملك شيك - فندر + نوتيلا', 'category' => 'drink', 'price' => 2.00],
            ['name' => 'ملك شيك - زبدة عربية', 'category' => 'drink', 'price' => 2.50],
            ['name' => 'ملك شيك - سنيرز حليب', 'category' => 'drink', 'price' => 2.00],
            ['name' => 'ملك شيك - لوتس', 'category' => 'drink', 'price' => 2.00],

            ['name' => 'شيك - سادة', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - موز', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - فراولة', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - مانجا', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - أفوكادو', 'category' => 'drink', 'price' => 1.50],
            ['name' => 'شيك - جوافة', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - أناناس', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - خوخ', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - ليمون', 'category' => 'drink', 'price' => 1.00],
            ['name' => 'شيك - ليمون نعناع', 'category' => 'drink', 'price' => 1.50],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(['name' => $item['name']], $item + ['is_active' => true]);
        }
    }
}
