<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Models\CampaignTarget;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // 1. Create Users
        $this->createUsers();

        // 2. Create Categories
        $categories = $this->createCategories();

        // 3. Create Colors
        $colors = $this->createColors();

        // 4. Create Sizes
        $sizes = $this->createSizes();

        // 5. Create Products
        $products = $this->createProducts($categories);

        // 6. Create Product Variants
        $this->createProductVariants();

        // 7. Create Discount Codes
        $this->createDiscountCodes();

        // 8. Create Campaigns
        $this->createCampaigns($products, $categories);

        // 9. Create Delivery Methods
        $this->call(DeliveryMethodSeeder::class);

        // 10. Create Sample Orders
        $this->createSampleOrders();

        // 11. Run Production Seeder (optional - comment out if not needed)
        $this->command->info('🏭 Running Production Seeder...');
        $this->call(ProductionSeeder::class);

        $this->command->info('✅ Database seeding completed successfully!');
    }

    private function createUsers()
    {
        $this->command->info('👤 Creating users...');

        User::updateOrCreate(
            ['phone' => '09123456789'],
            [
                'name' => 'مدیر سیستم', 
                'instagram_id' => '@admin',
                'password' => bcrypt('password'), 
                'is_admin' => true
            ]
        );

        User::updateOrCreate(
            ['phone' => '09123456790'],
            [
                'name' => 'کاربر تست',
                'instagram_id' => '@testuser', 
                'password' => bcrypt('password')
            ]
        );

        // Create additional test users
        User::factory(5)->create();
    }

    private function createCategories()
    {
        $this->command->info('📂 Creating categories...');

        $categories = [
            ['name' => 'تیشرت'],
            ['name' => 'شلوار'],
            ['name' => 'کفش'],
            ['name' => 'اکسسوری'],
            ['name' => 'لباس زمستانی'],
            ['name' => 'لباس تابستانی'],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);
            $createdCategories[] = $category;
        }

        return $createdCategories;
    }

    private function createColors()
    {
        $this->command->info('🎨 Creating colors...');

        $colors = [
            ['name' => 'قرمز', 'hex_code' => '#EF4444'],
            ['name' => 'آبی', 'hex_code' => '#3B82F6'],
            ['name' => 'سبز', 'hex_code' => '#10B981'],
            ['name' => 'مشکی', 'hex_code' => '#1F2937'],
            ['name' => 'سفید', 'hex_code' => '#F9FAFB'],
            ['name' => 'خاکستری', 'hex_code' => '#6B7280'],
            ['name' => 'صورتی', 'hex_code' => '#EC4899'],
            ['name' => 'بنفش', 'hex_code' => '#8B5CF6'],
        ];

        $createdColors = [];
        foreach ($colors as $colorData) {
            $color = Color::create($colorData);
            $createdColors[] = $color;
        }

        return $createdColors;
    }

    private function createSizes()
    {
        $this->command->info('📏 Creating sizes...');

        $sizes = [
            ['name' => 'XS', 'description' => 'Extra Small'],
            ['name' => 'S', 'description' => 'Small'],
            ['name' => 'M', 'description' => 'Medium'],
            ['name' => 'L', 'description' => 'Large'],
            ['name' => 'XL', 'description' => 'Extra Large'],
            ['name' => 'XXL', 'description' => 'Double Extra Large'],
        ];

        $createdSizes = [];
        foreach ($sizes as $sizeData) {
            $size = Size::create($sizeData);
            $createdSizes[] = $size;
        }

        return $createdSizes;
    }

    private function createProducts($categories)
    {
        $this->command->info('🛍️ Creating products...');

        $productNames = [
            'تیشرت کتان آبی',
            'شلوار جین مشکی',
            'کفش ورزشی سفید',
            'کاپشن زمستانی',
            'دستبند نقره',
            'گردنبند طلا',
            'ساعت هوشمند',
            'کلاه بافتنی',
            'شورت ورزشی',
            'پیراهن مردانه',
            'کت شلوار رسمی',
            'کفش کتانی رنگی',
            'ساک دستی چرمی',
            'عینک آفتابی',
            'کیف پول چرمی',
        ];

        $createdProducts = [];
        for ($i = 0; $i < 30; $i++) {
            $category = $categories[array_rand($categories)];
            $name = $productNames[array_rand($productNames)] . ' ' . ($i + 1);
            
            $product = Product::create([
                'category_id' => $category->id,
                'title' => $name,
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'description' => 'توضیحات کامل محصول ' . $name . '. این محصول با کیفیت بالا و طراحی زیبا تولید شده است.',
                'price' => rand(50000, 500000),
                'stock' => rand(0, 100),
                'has_variants' => rand(0, 1),
                'has_colors' => rand(0, 1),
                'has_sizes' => rand(0, 1),
                'is_active' => true,
            ]);

            // Add product images
            $imageCount = rand(1, 3);
            for ($j = 0; $j < $imageCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'https://picsum.photos/seed/' . md5($product->id . '-' . $j) . '/600/600',
                    'sort_order' => $j,
                ]);
            }

            $createdProducts[] = $product;
        }

        return $createdProducts;
    }

    private function createProductVariants()
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

    private function createDiscountCodes()
    {
        $this->command->info('🎫 Creating discount codes...');

        $discountCodes = [
            [
                'code' => 'WELCOME20',
                'type' => 'percentage',
                'value' => 20,
                'usage_limit' => 100,
                'max_discount_amount' => 50000,
                'min_order_amount' => 100000,
                'expires_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SUMMER50',
                'type' => 'percentage',
                'value' => 50,
                'usage_limit' => 50,
                'max_discount_amount' => 100000,
                'min_order_amount' => 200000,
                'expires_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'FIXED10K',
                'type' => 'fixed',
                'value' => 10000,
                'usage_limit' => 200,
                'min_order_amount' => 50000,
                'expires_at' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],
            [
                'code' => DiscountCode::generateCode(),
                'type' => 'percentage',
                'value' => 15,
                'usage_limit' => null,
                'max_discount_amount' => 25000,
                'expires_at' => Carbon::now()->addDays(15),
                'is_active' => true,
            ],
        ];

        foreach ($discountCodes as $codeData) {
            DiscountCode::create($codeData);
        }
    }

    private function createCampaigns($products, $categories)
    {
        $this->command->info('🎯 Creating campaigns...');

        // Campaign 1: Summer Sale
        $summerCampaign = Campaign::create([
            'name' => 'فروش فوق‌العاده تابستان',
            'description' => 'تخفیف ویژه محصولات تابستانی تا 40 درصد',
            'type' => 'percentage',
            'discount_value' => 30,
            'max_discount_amount' => 75000,
            'starts_at' => Carbon::now()->subDays(5),
            'ends_at' => Carbon::now()->addDays(25),
            'is_active' => true,
            'priority' => 10,
            'badge_text' => 'فروش تابستان',
        ]);

        // Add targets for summer campaign (some products and categories)
        $summerProducts = array_slice($products, 0, 8);
        foreach ($summerProducts as $product) {
            CampaignTarget::create([
                'campaign_id' => $summerCampaign->id,
                'targetable_type' => Product::class,
                'targetable_id' => $product->id,
            ]);
        }

        // Campaign 2: T-shirt Special
        $tshirtCampaign = Campaign::create([
            'name' => 'تخفیف ویژه تیشرت‌ها',
            'description' => 'همه تیشرت‌ها با 25 درصد تخفیف',
            'type' => 'percentage',
            'discount_value' => 25,
            'max_discount_amount' => 50000,
            'starts_at' => Carbon::now()->subDays(2),
            'ends_at' => Carbon::now()->addDays(15),
            'is_active' => true,
            'priority' => 5,
            'badge_text' => 'تیشرت ارزان',
        ]);

        // Add t-shirt category as target
        $tshirtCategory = collect($categories)->first();
        CampaignTarget::create([
            'campaign_id' => $tshirtCampaign->id,
            'targetable_type' => Category::class,
            'targetable_id' => $tshirtCategory->id,
        ]);

        // Campaign 3: Fixed Discount
        $fixedCampaign = Campaign::create([
            'name' => 'تخفیف 20 هزار تومانی',
            'description' => 'تخفیف ثابت 20 هزار تومان برای محصولات انتخاب شده',
            'type' => 'fixed',
            'discount_value' => 20000,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(10),
            'is_active' => true,
            'priority' => 3,
            'badge_text' => '20K تخفیف',
        ]);

        // Add some products for fixed discount
        $fixedProducts = array_slice($products, 10, 5);
        foreach ($fixedProducts as $product) {
            CampaignTarget::create([
                'campaign_id' => $fixedCampaign->id,
                'targetable_type' => Product::class,
                'targetable_id' => $product->id,
            ]);
        }

        // Campaign 4: Upcoming Campaign
        $upcomingCampaign = Campaign::create([
            'name' => 'فروش ویژه زمستان',
            'description' => 'آماده شوید برای تخفیف‌های زمستانی',
            'type' => 'percentage',
            'discount_value' => 40,
            'max_discount_amount' => 100000,
            'starts_at' => Carbon::now()->addDays(5),
            'ends_at' => Carbon::now()->addDays(35),
            'is_active' => true,
            'priority' => 15,
            'badge_text' => 'به زودی',
        ]);

        // Add winter category
        $winterCategory = collect($categories)->where('name', 'لباس زمستانی')->first();
        if ($winterCategory) {
            CampaignTarget::create([
                'campaign_id' => $upcomingCampaign->id,
                'targetable_type' => Category::class,
                'targetable_id' => $winterCategory->id,
            ]);
        }
    }

    private function createSampleOrders()
    {
        $this->command->info('🛒 Creating sample orders...');

        $users = User::where('is_admin', false)->get();
        $products = Product::where('is_active', true)->get();
        $campaigns = Campaign::where('is_active', true)->get();
        $discountCodes = DiscountCode::where('is_active', true)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️ No users or products found. Skipping order creation.');
            return;
        }

        $campaignService = new \App\Services\CampaignService();

        // Create 20-30 sample orders
        $orderCount = rand(20, 30);
        $userIndex = 0;
        $shuffledUsers = $users->shuffle();
        
        for ($i = 0; $i < $orderCount; $i++) {
            // Cycle through users to ensure better distribution
            $user = $shuffledUsers[$userIndex % $shuffledUsers->count()];
            $userIndex++;
            $orderDate = \Carbon\Carbon::now()->subDays(rand(1, 30));

            // Create order
            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'customer_address' => 'آدرس نمونه - ' . rand(1, 100),
                'total_amount' => 0, // Will be calculated
                'final_amount' => 0, // Will be calculated
                'status' => $this->getRandomStatus(),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1-4 items to order
            $itemCount = rand(1, 4);
            $selectedProducts = $products->random($itemCount);
            $totalAmount = 0;

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $originalPrice = $product->price;

                // Check if product has campaign discount
                $campaignData = $campaignService->calculateProductPrice($product);
                $unitPrice = $campaignData['campaign_price'];
                $campaignDiscountAmount = $campaignData['discount_amount'];
                $campaignId = $campaignData['campaign']?->id;

                $lineTotal = $unitPrice * $quantity;
                $totalAmount += $lineTotal;

                // Create order item
                $orderItem = \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'campaign_id' => $campaignId,
                    'original_price' => $originalPrice,
                    'campaign_discount_amount' => $campaignDiscountAmount,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Record campaign sale if applicable
                if ($campaignId) {
                    \App\Models\CampaignSale::create([
                        'campaign_id' => $campaignId,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $product->id,
                        'original_price' => $originalPrice,
                        'discount_amount' => $campaignDiscountAmount,
                        'final_price' => $unitPrice,
                        'quantity' => $quantity,
                        'total_discount' => $campaignDiscountAmount * $quantity,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }
            }

            // Apply discount code to some orders
            $finalAmount = $totalAmount;
            $discountAmount = 0;
            $discountCode = null;

            if (rand(0, 3) === 0 && $discountCodes->isNotEmpty() && $totalAmount > 50000) {
                // Find a discount code that this user hasn't used yet
                $usedDiscountCodeIds = \App\Models\DiscountCodeUsage::where('user_id', $user->id)
                    ->pluck('discount_code_id')
                    ->toArray();
                
                $availableDiscountCodes = $discountCodes->whereNotIn('id', $usedDiscountCodeIds)
                    ->filter(function ($code) use ($totalAmount) {
                        return $code->calculateDiscount($totalAmount) > 0;
                    });
                
                if ($availableDiscountCodes->isNotEmpty()) {
                    $discountCode = $availableDiscountCodes->random();
                    $discountAmount = min($discountCode->calculateDiscount($totalAmount), $totalAmount);
                    $finalAmount = $totalAmount - $discountAmount;

                    // Create discount code usage
                    \App\Models\DiscountCodeUsage::create([
                        'discount_code_id' => $discountCode->id,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'discount_amount' => $discountAmount,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }
            }

            // Update order with final amounts
            $order->update([
                'total_amount' => $totalAmount,
                'discount_code' => $discountCode?->code,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
            ]);

            // Create invoice for completed orders
            if (in_array($order->status, ['completed', 'shipped'])) {
                $invoice = \App\Models\Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'amount' => $finalAmount,
                    'status' => 'paid',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create transaction
                \App\Models\Transaction::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $finalAmount,
                    'method' => 'bank_transfer',
                    'status' => 'verified',
                    'reference' => 'TXN-' . rand(100000, 999999),
                    'verified_at' => $orderDate,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['pending', 'processing', 'completed', 'shipped', 'delivered', 'cancelled'];
        $weights = [20, 25, 30, 15, 8, 2]; // Probability weights

        $random = rand(1, 100);
        $cumulative = 0;

        for ($i = 0; $i < count($statuses); $i++) {
            $cumulative += $weights[$i];
            if ($random <= $cumulative) {
                return $statuses[$i];
            }
        }

        return 'pending';
    }
}
