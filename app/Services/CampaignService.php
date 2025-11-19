<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSale;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function getActiveCampaigns(): \Illuminate\Database\Eloquent\Collection
    {
        return Campaign::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function getUpcomingCampaigns(): \Illuminate\Database\Eloquent\Collection
    {
        return Campaign::where('is_active', true)
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->get();
    }

    public function getProductsWithCampaigns()
    {
        $activeCampaigns = $this->getActiveCampaigns();
        $products = collect();

        foreach ($activeCampaigns as $campaign) {
            $affectedProducts = $campaign->getAffectedProducts();
            $products = $products->merge($affectedProducts);
        }

        return $products->unique('id');
    }

    public function calculateProductPrice(Product $product): array
    {
        $campaign = $product->best_campaign;
        
        if (!$campaign) {
            return [
                'original_price' => $product->price,
                'campaign_price' => $product->price,
                'discount_amount' => 0,
                'campaign' => null,
                'has_discount' => false,
            ];
        }

        $originalPrice = $product->price;
        $discountAmount = $campaign->calculateDiscount($originalPrice);
        $campaignPrice = $originalPrice - $discountAmount;

        return [
            'original_price' => $originalPrice,
            'campaign_price' => $campaignPrice,
            'discount_amount' => $discountAmount,
            'campaign' => $campaign,
            'has_discount' => $discountAmount > 0,
        ];
    }

    public function calculateVariantPrice($productVariant): array
    {
        $product = $productVariant->product;
        $campaign = $product->best_campaign;
        
        if (!$campaign) {
            return [
                'original_price' => $productVariant->price,
                'campaign_price' => $productVariant->price,
                'discount_amount' => 0,
                'campaign' => null,
                'has_discount' => false,
            ];
        }

        $originalPrice = $productVariant->price;
        $discountAmount = $campaign->calculateDiscount($originalPrice);
        $campaignPrice = $originalPrice - $discountAmount;

        return [
            'original_price' => $originalPrice,
            'campaign_price' => $campaignPrice,
            'discount_amount' => $discountAmount,
            'campaign' => $campaign,
            'has_discount' => $discountAmount > 0,
        ];
    }

    public function recordCampaignSale(OrderItem $orderItem): void
    {
        if (!$orderItem->campaign_id) {
            return;
        }

        DB::transaction(function () use ($orderItem) {
            CampaignSale::create([
                'campaign_id' => $orderItem->campaign_id,
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'original_price' => $orderItem->original_price ?? $orderItem->unit_price,
                'discount_amount' => $orderItem->campaign_discount_amount ?? 0,
                'final_price' => $orderItem->unit_price,
                'quantity' => $orderItem->quantity,
                'total_discount' => ($orderItem->campaign_discount_amount ?? 0) * $orderItem->quantity,
            ]);
        });
    }

    public function getCampaignAnalytics(int $campaignId, $startDate = null, $endDate = null): array
    {
        $query = CampaignSale::where('campaign_id', $campaignId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->count(),
            'total_quantity' => $sales->sum('quantity'),
            'total_revenue' => $sales->sum(function ($sale) {
                return $sale->final_price * $sale->quantity;
            }),
            'total_discount' => $sales->sum('total_discount'),
            'average_order_value' => $sales->count() > 0 ? $sales->sum(function ($sale) {
                return $sale->final_price * $sale->quantity;
            }) / $sales->count() : 0,
            'products_sold' => $sales->groupBy('product_id')->map(function ($productSales) {
                return [
                    'product_id' => $productSales->first()->product_id,
                    'product_name' => $productSales->first()->product->title,
                    'quantity_sold' => $productSales->sum('quantity'),
                    'revenue' => $productSales->sum(function ($sale) {
                        return $sale->final_price * $sale->quantity;
                    }),
                    'discount_given' => $productSales->sum('total_discount'),
                ];
            })->values(),
            'daily_sales' => $sales->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            })->map(function ($daySales) {
                return [
                    'date' => $daySales->first()->created_at->format('Y-m-d'),
                    'quantity' => $daySales->sum('quantity'),
                    'revenue' => $daySales->sum(function ($sale) {
                        return $sale->final_price * $sale->quantity;
                    }),
                    'orders' => $daySales->unique('order_item_id')->count(),
                ];
            })->values(),
        ];
    }

    public function getGlobalCampaignStats($startDate = null, $endDate = null): array
    {
        $query = CampaignSale::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $sales = $query->get();

        return [
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => $this->getActiveCampaigns()->count(),
            'total_sales' => $sales->count(),
            'total_quantity' => $sales->sum('quantity'),
            'total_revenue' => $sales->sum(function ($sale) {
                return $sale->final_price * $sale->quantity;
            }),
            'total_discount' => $sales->sum('total_discount'),
            'top_campaigns' => $sales->groupBy('campaign_id')->map(function ($campaignSales) {
                $campaign = $campaignSales->first()->campaign;
                return [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'sales_count' => $campaignSales->count(),
                    'quantity_sold' => $campaignSales->sum('quantity'),
                    'revenue' => $campaignSales->sum(function ($sale) {
                        return $sale->final_price * $sale->quantity;
                    }),
                    'discount_given' => $campaignSales->sum('total_discount'),
                ];
            })->sortByDesc('revenue')->take(10)->values(),
        ];
    }
}
