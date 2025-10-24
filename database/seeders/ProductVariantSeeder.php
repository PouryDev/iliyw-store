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
        $this->command->info('🖼️ Creating artwork variants (frame types & dimensions)...');

        $products = Product::where('has_variants', true)->get();
        $colors = Color::all(); // Frame types
        $sizes = Size::all(); // Dimensions

        if ($products->isEmpty() || $colors->isEmpty() || $sizes->isEmpty()) {
            $this->command->warn('⚠️ No products with variants, frame types, or dimensions found. Skipping variant creation.');
            return;
        }

        foreach ($products as $product) {
            $this->command->info("Creating variants for artwork: {$product->title}");

            // Determine if product has frame types and/or dimensions
            $hasFrameTypes = $product->has_colors && $colors->isNotEmpty();
            $hasDimensions = $product->has_sizes && $sizes->isNotEmpty();

            if (!$hasFrameTypes && !$hasDimensions) {
                continue;
            }

            // Create variants based on product configuration
            if ($hasFrameTypes && $hasDimensions) {
                // Product has both frame types and dimensions
                $selectedFrames = $colors->random(rand(2, 4));
                $selectedDimensions = $sizes->random(rand(2, 4));

                foreach ($selectedFrames as $frame) {
                    foreach ($selectedDimensions as $dimension) {
                        $this->createVariant($product, $frame, $dimension);
                    }
                }
            } elseif ($hasFrameTypes) {
                // Product has only frame types
                $selectedFrames = $colors->random(rand(2, 5));
                foreach ($selectedFrames as $frame) {
                    $this->createVariant($product, $frame, null);
                }
            } elseif ($hasDimensions) {
                // Product has only dimensions
                $selectedDimensions = $sizes->random(rand(2, 5));
                foreach ($selectedDimensions as $dimension) {
                    $this->createVariant($product, null, $dimension);
                }
            }
        }

        $this->command->info('✅ Artwork variants created successfully!');
    }

    private function createVariant($product, $color = null, $size = null)
    {
        $basePrice = $product->price;
        
        // Price variation based on frame type and dimensions
        // Larger sizes and special frames cost more
        $priceVariation = rand(-50000, 150000); // Wider range for art pieces
        $variantPrice = $basePrice + $priceVariation;
        $variantPrice = max($variantPrice, 150000); // Higher minimum price for artworks

        $stock = rand(1, 12); // Lower stock for art pieces

        ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color?->id, // Frame type
            'size_id' => $size?->id, // Dimensions
            'stock' => $stock,
            'price' => $variantPrice,
            'is_active' => true,
        ]);
    }
}