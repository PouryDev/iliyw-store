<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index()
    {
        $q = request('q');
        $products = Product::query()
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('title', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->where('is_active', true)
            ->latest()
            ->paginate(12)
            ->withQueryString();
            
        // Get active campaigns for banner display
        $campaignService = new \App\Services\CampaignService();
        $activeCampaigns = $campaignService->getActiveCampaigns();
        
        return view('shop.index', compact('products', 'activeCampaigns'));
    }

    public function show(Product $product): View
    {
        $product->load(['images', 'activeVariants.color', 'activeVariants.size']);
        return view('shop.show', compact('product'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        $page = $request->input('page', 1);
        
        $products = Product::query()
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('title', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->where('is_active', true)
            ->latest()
            ->paginate(12, ['*'], 'page', $page)
            ->withQueryString();

        // Get active campaigns for banner display
        $campaignService = new \App\Services\CampaignService();
        $activeCampaigns = $campaignService->getActiveCampaigns();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('shop.partials.products', compact('products', 'activeCampaigns'))->render(),
                'hasMorePages' => $products->hasMorePages(),
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'total' => $products->total(),
            ]);
        }

        return view('shop.index', compact('products', 'activeCampaigns'));
    }
}


