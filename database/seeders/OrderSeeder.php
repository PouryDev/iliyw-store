<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Campaign;
use App\Models\CampaignSale;
use App\Models\DiscountCode;
use App\Models\DiscountCodeUsage;
use App\Services\CampaignService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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

        $campaignService = new CampaignService();

        // Create 20-30 sample orders
        $orderCount = rand(20, 30);
        $userIndex = 0;
        $shuffledUsers = $users->shuffle();
        
        for ($i = 0; $i < $orderCount; $i++) {
            // Cycle through users to ensure better distribution
            $user = $shuffledUsers[$userIndex % $shuffledUsers->count()];
            $userIndex++;
            $orderDate = Carbon::now()->subDays(rand(1, 30));

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'customer_address' => 'آدرس نمونه - ' . rand(1, 100),
                'total_amount' => 0, // Will be calculated
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
                $orderItem = OrderItem::create([
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
                    CampaignSale::create([
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
                $usedDiscountCodeIds = DiscountCodeUsage::where('user_id', $user->id)
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
                    DiscountCodeUsage::create([
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
                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'amount' => $finalAmount,
                    'status' => 'paid',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create transaction
                Transaction::create([
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

        $this->command->info('✅ Sample orders created successfully!');
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