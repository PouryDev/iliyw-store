<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎨 Creating product variants...');

        $products = Product::where('has_variants', true)->get();
        $colors = Color::all();
        $sizes = Size::all();

        if ($products->isEmpty() || $colors->isEmpty() || $sizes->isEmpty()) {
            $this->command->warn('⚠️ No products with variants, colors, or sizes found. Skipping variant creation.');
            return;
        }

        foreach ($products as $product) {
            $this->command->info("Creating variants for: {$product->title}");

            // Determine if product has colors and/or sizes
            $hasColors = $product->has_colors && $colors->isNotEmpty();
            $hasSizes = $product->has_sizes && $sizes->isNotEmpty();

            if (!$hasColors && !$hasSizes) {
                continue;
            }

            // Create variants based on product configuration
            if ($hasColors && $hasSizes) {
                // Product has both colors and sizes
                $selectedColors = $colors->random(rand(2, 4));
                $selectedSizes = $sizes->random(rand(2, 4));

                foreach ($selectedColors as $color) {
                    foreach ($selectedSizes as $size) {
                        $this->createVariant($product, $color, $size);
                    }
                }
            } elseif ($hasColors) {
                // Product has only colors
                $selectedColors = $colors->random(rand(2, 5));
                foreach ($selectedColors as $color) {
                    $this->createVariant($product, $color, null);
                }
            } elseif ($hasSizes) {
                // Product has only sizes
                $selectedSizes = $sizes->random(rand(2, 5));
                foreach ($selectedSizes as $size) {
                    $this->createVariant($product, null, $size);
                }
            }
        }

        $this->command->info('✅ Product variants created successfully!');
    }

    private function createVariant($product, $color = null, $size = null)
    {
        $basePrice = $product->price;
        $variantPrice = $basePrice + rand(-10000, 20000); // Slight price variation
        $variantPrice = max($variantPrice, 10000); // Minimum price

        $stock = rand(0, 50);

        ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color?->id,
            'size_id' => $size?->id,
            'stock' => $stock,
            'price' => $variantPrice,
            'is_active' => true,
        ]);
    }
}