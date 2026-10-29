<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class SystemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['type' => 'income', 'name' => 'Gaji', 'slug' => 'system-gaji', 'icon' => 'banknotes', 'color' => '#059669'],
            ['type' => 'income', 'name' => 'Bonus', 'slug' => 'system-bonus', 'icon' => 'gift', 'color' => '#0d9488'],
            ['type' => 'income', 'name' => 'Usaha', 'slug' => 'system-usaha', 'icon' => 'briefcase', 'color' => '#0284c7'],
            ['type' => 'income', 'name' => 'Investasi', 'slug' => 'system-investasi', 'icon' => 'chart-bar', 'color' => '#7c3aed'],
            ['type' => 'income', 'name' => 'Lainnya', 'slug' => 'system-income-lainnya', 'icon' => 'plus-circle', 'color' => '#64748b'],
            ['type' => 'expense', 'name' => 'Makan & Minum', 'slug' => 'system-makan', 'icon' => 'cake', 'color' => '#f59e0b'],
            ['type' => 'expense', 'name' => 'Transportasi', 'slug' => 'system-transportasi', 'icon' => 'truck', 'color' => '#0ea5e9'],
            ['type' => 'expense', 'name' => 'Belanja', 'slug' => 'system-belanja', 'icon' => 'shopping-bag', 'color' => '#ec4899'],
            ['type' => 'expense', 'name' => 'Tagihan', 'slug' => 'system-tagihan', 'icon' => 'receipt-percent', 'color' => '#ef4444'],
            ['type' => 'expense', 'name' => 'Kesehatan', 'slug' => 'system-kesehatan', 'icon' => 'heart', 'color' => '#f43f5e'],
            ['type' => 'expense', 'name' => 'Pendidikan', 'slug' => 'system-pendidikan', 'icon' => 'academic-cap', 'color' => '#8b5cf6'],
            ['type' => 'expense', 'name' => 'Hiburan', 'slug' => 'system-hiburan', 'icon' => 'film', 'color' => '#d946ef'],
            ['type' => 'expense', 'name' => 'Rumah', 'slug' => 'system-rumah', 'icon' => 'home', 'color' => '#84cc16'],
            ['type' => 'expense', 'name' => 'Keluarga', 'slug' => 'system-keluarga', 'icon' => 'users', 'color' => '#14b8a6'],
            ['type' => 'expense', 'name' => 'Lainnya', 'slug' => 'system-expense-lainnya', 'icon' => 'ellipsis-horizontal', 'color' => '#64748b'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'user_id' => null,
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'is_system' => true,
                    'is_active' => true,
                ],
            );
        }
    }
}
