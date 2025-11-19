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
                'title' => 'پست پیشتاز',
                'fee' => 25000,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'پست سفارشی',
                'fee' => 35000,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'پیک موتوری (تهران)',
                'fee' => 45000,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'ارسال رایگان (خرید بالای 500 هزار تومان)',
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
