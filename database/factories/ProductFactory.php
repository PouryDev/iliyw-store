<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $artNames = [
            'غروب طلایی', 'رویای آبی', 'شب مهتابی', 'باغ بهشت', 'نقش و نگار',
            'افق طلایی', 'سکوت سبز', 'رقص رنگ‌ها', 'نسیم صبح', 'ابریشم شب',
            'موج‌های آرامش', 'درخت زندگی', 'پرتره مدرن', 'انتزاع هندسی', 'خیال پردازی',
            'چشم‌انداز کوه', 'دریای آرام', 'جنگل پاییزی', 'شهر شب', 'نور و سایه'
        ];

        $artists = [
            'رضا محمدی', 'سارا احمدی', 'علی کریمی', 'مریم نوری',
            'حسین صادقی', 'فاطمه رضایی', 'امیر حسینی', 'نگار محمودی'
        ];

        $techniques = [
            'آکریلیک روی بوم', 'رنگ روغن', 'آبرنگ',
            'چاپ دیجیتال با کیفیت موزه‌ای', 'تکنیک مدرن', 'تکنیک مخلوط'
        ];

        $title = fake()->randomElement($artNames) . ' ' . fake()->unique()->numberBetween(100, 999);
        $isMusical = fake()->boolean(20); // 20% chance of being musical

        return [
            'title' => $title,
            'slug' => Str::slug($title . '-' . Str::random(4)),
            'description' => 'این اثر هنری با الهام از زیبایی‌های طبیعت و احساسات انسانی خلق شده است. ' .
                           'هر تابلو با دقت و ظرافت خاصی ساخته شده و می‌تواند فضای منزل یا محل کار شما را متحول کند.' .
                           ($isMusical ? ' این تابلو موزیکال با موسیقی‌های منتخب همراه است.' : ''),
            'price' => fake()->numberBetween(180000, 2800000),
            'stock' => fake()->numberBetween(1, 15),
            'is_active' => true,
            'is_musical' => $isMusical,
            'artist' => fake()->randomElement($artists),
            'technique' => fake()->randomElement($techniques),
            'year' => fake()->numberBetween(2020, 2024),
        ];
    }
}


