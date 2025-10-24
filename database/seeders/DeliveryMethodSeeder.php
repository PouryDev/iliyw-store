<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $methods = [
            [
                'title' => 'پست پیشتاز (بسته‌بندی ویژه)',
                'fee' => 35000,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'پست سفارشی (بیمه شده)',
                'fee' => 50000,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'پیک موتوری تهران (حرفه‌ای)',
                'fee' => 65000,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'ارسال رایگان (خرید بالای 2 میلیون تومان)',
                'fee' => 0,
                'is_active' => true,
                'sort_order' => 0,
            ],
        ];

        foreach ($methods as $method) {
            DeliveryMethod::create($method);
        }
    }
}
