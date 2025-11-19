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
        $title = fake('fa_IR')->randomElement([
            'گردنبند مینیمال', 'دستبند ظریف', 'گوشواره دخترانه', 'انگشتر استیل', 'ست اکسسوری'
        ]) . ' ' . fake()->unique()->numberBetween(100, 999);

        return [
            'title' => $title,
            'slug' => Str::slug($title . '-' . Str::random(4)),
            'description' => fake('fa_IR')->paragraph(),
            'price' => fake()->numberBetween(80000, 850000),
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}


