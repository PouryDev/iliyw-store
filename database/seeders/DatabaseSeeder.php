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
            ['name' => 'نقاشی انتزاعی'],
            ['name' => 'منظره طبیعت'],
            ['name' => 'پرتره و چهره'],
            ['name' => 'هنر مدرن'],
            ['name' => 'تابلوهای موزیکال'],
            ['name' => 'آثار دیجیتال'],
            ['name' => 'خوشنویسی مدرن'],
            ['name' => 'مینیمالیستی'],
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
        $this->command->info('🖼️ Creating frame types (colors table)...');

        // Colors table is used for frame types
        $colors = [
            ['name' => 'قاب چوبی طبیعی', 'hex_code' => '#D2691E'],
            ['name' => 'قاب چوبی تیره', 'hex_code' => '#654321'],
            ['name' => 'قاب فلزی طلایی', 'hex_code' => '#FFD700'],
            ['name' => 'قاب فلزی نقره‌ای', 'hex_code' => '#C0C0C0'],
            ['name' => 'قاب فلزی مشکی', 'hex_code' => '#1F2937'],
            ['name' => 'قاب سفید مدرن', 'hex_code' => '#F9FAFB'],
            ['name' => 'بدون قاب (کشیده روی تخته)', 'hex_code' => '#FFFFFF'],
            ['name' => 'قاب چوبی شیک', 'hex_code' => '#8B4513'],
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
        $this->command->info('📐 Creating dimensions (sizes table)...');

        // Sizes table is used for painting dimensions
        $sizes = [
            ['name' => '20×30 سانتی‌متر', 'description' => 'مناسب برای فضاهای کوچک'],
            ['name' => '30×40 سانتی‌متر', 'description' => 'سایز استاندارد کوچک'],
            ['name' => '40×60 سانتی‌متر', 'description' => 'سایز متوسط محبوب'],
            ['name' => '50×70 سانتی‌متر', 'description' => 'سایز متوسط بزرگ'],
            ['name' => '60×90 سانتی‌متر', 'description' => 'سایز بزرگ'],
            ['name' => '70×100 سانتی‌متر', 'description' => 'سایز خیلی بزرگ'],
            ['name' => '80×120 سانتی‌متر', 'description' => 'سایز بزرگ دیواری'],
            ['name' => '100×150 سانتی‌متر', 'description' => 'سایز پانوراما'],
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
        $this->command->info('🎨 Creating artwork products...');

        $productNames = [
            'غروب آرام',
            'رویای آبی',
            'شب مهتابی',
            'باغ بهشت',
            'نقش و نگار',
            'افق طلایی',
            'سکوت سبز',
            'رقص رنگ‌ها',
            'نسیم صبح',
            'ابریشم شب',
            'موج‌های آرامش',
            'درخت زندگی',
            'پرتره مدرن',
            'انتزاع هندسی',
            'خیال پردازی',
        ];

        $artistNames = [
            'رضا محمدی',
            'سارا احمدی',
            'علی کریمی',
            'مریم نوری',
            'حسین صادقی',
            'فاطمه رضایی',
            'امیر حسینی',
            'نگار محمودی',
        ];

        $techniques = [
            'آکریلیک روی بوم',
            'رنگ روغن',
            'آبرنگ',
            'چاپ دیجیتال با کیفیت موزه‌ای',
            'تکنیک مدرن',
            'تکنیک مخلوط',
        ];

        $createdProducts = [];
        for ($i = 0; $i < 40; $i++) {
            $category = $categories[array_rand($categories)];
            $name = $productNames[array_rand($productNames)] . ' ' . ($i + 1);
            $isMusical = ($category->name === 'تابلوهای موزیکال') || (rand(0, 4) === 0); // 20% musical
            
            $product = Product::create([
                'category_id' => $category->id,
                'title' => $name,
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'description' => 'این اثر هنری با الهام از زیبایی‌های طبیعت و احساسات انسانی خلق شده است. ' . 
                               'هر تابلو با دقت و ظرافت خاصی ساخته شده و می‌تواند فضای منزل یا محل کار شما را متحول کند. ' .
                               ($isMusical ? 'این تابلو موزیکال با موسیقی‌های منتخب همراه است که تجربه‌ای منحصر به فرد را خلق می‌کند.' : ''),
                'price' => rand(150000, 2500000),
                'stock' => rand(1, 15),
                'has_variants' => rand(0, 1),
                'has_colors' => rand(0, 1), // Frame types
                'has_sizes' => rand(0, 1), // Dimensions
                'is_active' => true,
                'is_musical' => $isMusical,
                'artist' => $artistNames[array_rand($artistNames)],
                'technique' => $techniques[array_rand($techniques)],
                'year' => rand(2020, 2024),
            ]);

            // Add product images (art images)
            $imageCount = rand(2, 4);
            for ($j = 0; $j < $imageCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'https://picsum.photos/seed/' . md5($product->id . '-art-' . $j) . '/800/800',
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
                'code' => 'ILIYWART20',
                'type' => 'percentage',
                'value' => 20,
                'usage_limit' => 100,
                'max_discount_amount' => 200000,
                'min_order_amount' => 500000,
                'expires_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'MUSICAL30',
                'type' => 'percentage',
                'value' => 30,
                'usage_limit' => 50,
                'max_discount_amount' => 300000,
                'min_order_amount' => 800000,
                'expires_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'ART50K',
                'type' => 'fixed',
                'value' => 50000,
                'usage_limit' => 200,
                'min_order_amount' => 300000,
                'expires_at' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],
            [
                'code' => 'GALLERY15',
                'type' => 'percentage',
                'value' => 15,
                'usage_limit' => null,
                'max_discount_amount' => 150000,
                'min_order_amount' => 400000,
                'expires_at' => Carbon::now()->addDays(15),
                'is_active' => true,
            ],
            [
                'code' => DiscountCode::generateCode(),
                'type' => 'percentage',
                'value' => 25,
                'usage_limit' => 30,
                'max_discount_amount' => 250000,
                'expires_at' => Carbon::now()->addWeeks(3),
                'is_active' => true,
            ],
        ];

        foreach ($discountCodes as $codeData) {
            DiscountCode::create($codeData);
        }
    }

    private function createCampaigns($products, $categories)
    {
        $this->command->info('🎯 Creating art gallery campaigns...');

        // Campaign 1: Art Gallery Opening
        $openingCampaign = Campaign::create([
            'name' => 'افتتاحیه گالری',
            'description' => 'تخفیف ویژه آثار هنری منتخب تا 35 درصد',
            'type' => 'percentage',
            'discount_value' => 35,
            'max_discount_amount' => 400000,
            'starts_at' => Carbon::now()->subDays(5),
            'ends_at' => Carbon::now()->addDays(25),
            'is_active' => true,
            'priority' => 10,
            'badge_text' => 'افتتاحیه',
        ]);

        // Add targets for opening campaign
        $openingProducts = array_slice($products, 0, 10);
        foreach ($openingProducts as $product) {
            CampaignTarget::create([
                'campaign_id' => $openingCampaign->id,
                'targetable_type' => Product::class,
                'targetable_id' => $product->id,
            ]);
        }

        // Campaign 2: Musical Paintings Special
        $musicalCampaign = Campaign::create([
            'name' => 'تخفیف ویژه تابلوهای موزیکال',
            'description' => 'همه تابلوهای موزیکال با 30 درصد تخفیف',
            'type' => 'percentage',
            'discount_value' => 30,
            'max_discount_amount' => 350000,
            'starts_at' => Carbon::now()->subDays(2),
            'ends_at' => Carbon::now()->addDays(15),
            'is_active' => true,
            'priority' => 8,
            'badge_text' => 'موزیکال',
        ]);

        // Add musical category as target
        $musicalCategory = collect($categories)->where('name', 'تابلوهای موزیکال')->first();
        if ($musicalCategory) {
            CampaignTarget::create([
                'campaign_id' => $musicalCampaign->id,
                'targetable_type' => Category::class,
                'targetable_id' => $musicalCategory->id,
            ]);
        }

        // Campaign 3: Fixed Discount for Modern Art
        $modernCampaign = Campaign::create([
            'name' => 'تخفیف 100 هزار تومانی',
            'description' => 'تخفیف ثابت 100 هزار تومان برای آثار مدرن منتخب',
            'type' => 'fixed',
            'discount_value' => 100000,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->addDays(10),
            'is_active' => true,
            'priority' => 6,
            'badge_text' => '100K تخفیف',
        ]);

        // Add modern art category
        $modernCategory = collect($categories)->where('name', 'هنر مدرن')->first();
        if ($modernCategory) {
            CampaignTarget::create([
                'campaign_id' => $modernCampaign->id,
                'targetable_type' => Category::class,
                'targetable_id' => $modernCategory->id,
            ]);
        }

        // Campaign 4: Abstract Art Festival
        $abstractCampaign = Campaign::create([
            'name' => 'جشنواره هنر انتزاعی',
            'description' => 'تخفیف ویژه نقاشی‌های انتزاعی - 25 درصد',
            'type' => 'percentage',
            'discount_value' => 25,
            'max_discount_amount' => 300000,
            'starts_at' => Carbon::now()->subDays(3),
            'ends_at' => Carbon::now()->addDays(20),
            'is_active' => true,
            'priority' => 7,
            'badge_text' => 'انتزاعی',
        ]);

        // Add abstract category
        $abstractCategory = collect($categories)->where('name', 'نقاشی انتزاعی')->first();
        if ($abstractCategory) {
            CampaignTarget::create([
                'campaign_id' => $abstractCampaign->id,
                'targetable_type' => Category::class,
                'targetable_id' => $abstractCategory->id,
            ]);
        }

        // Campaign 5: Upcoming Winter Collection
        $winterCampaign = Campaign::create([
            'name' => 'کلکسیون زمستانی',
            'description' => 'آماده شوید برای کلکسیون جدید زمستانی با تخفیف‌های ویژه',
            'type' => 'percentage',
            'discount_value' => 40,
            'max_discount_amount' => 500000,
            'starts_at' => Carbon::now()->addDays(7),
            'ends_at' => Carbon::now()->addDays(37),
            'is_active' => true,
            'priority' => 15,
            'badge_text' => 'به زودی',
        ]);

        // Add some products for upcoming campaign
        $winterProducts = array_slice($products, 15, 8);
        foreach ($winterProducts as $product) {
            CampaignTarget::create([
                'campaign_id' => $winterCampaign->id,
                'targetable_type' => Product::class,
                'targetable_id' => $product->id,
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
