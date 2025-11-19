<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignProductController extends Controller
{
    public function show(Request $request, Campaign $campaign)
    {
        $page = $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 12);

        // Get affected products
        $products = $campaign->getAffectedProducts();
        
        // Load images and campaigns
        $productIds = $products->pluck('id');
        $productsQuery = \App\Models\Product::whereIn('id', $productIds)
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->where('is_active', true)
            ->latest();
        
        $paginatedProducts = $productsQuery->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
            'data' => $paginatedProducts->items(),
            'pagination' => [
                'current_page' => $paginatedProducts->currentPage(),
                'last_page' => $paginatedProducts->lastPage(),
                'per_page' => $paginatedProducts->perPage(),
                'total' => $paginatedProducts->total(),
                'has_more_pages' => $paginatedProducts->hasMorePages(),
            ]
        ]);
    }
}
