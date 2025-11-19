<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\Category;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $query = Campaign::query();

        if ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
        }

        if ($status) {
            switch ($status) {
                case 'active':
                    $query->where('is_active', true)
                          ->where('starts_at', '<=', now())
                          ->where('ends_at', '>=', now());
                    break;
                case 'upcoming':
                    $query->where('is_active', true)
                          ->where('starts_at', '>', now());
                    break;
                case 'expired':
                    $query->where('ends_at', '<', now());
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        $campaigns = $query->withCount(['sales', 'targets'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.campaigns.index', compact('campaigns', 'q', 'status'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('title')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.campaigns.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:0',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:999',
            'banner_image' => 'nullable|image|max:4096',
            'badge_text' => 'nullable|string|max:50',
            'targets' => 'required|array|min:1',
            'targets.*' => 'required|string',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('campaigns', 'public');
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $data['priority'] = $data['priority'] ?? 0;

        // Create campaign
        $campaign = Campaign::create($data);

        // Attach targets
        foreach ($data['targets'] as $target) {
            [$type, $id] = explode(':', $target);
            $campaign->targets()->create([
                'targetable_type' => $type === 'product' ? Product::class : Category::class,
                'targetable_id' => $id,
            ]);
        }

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'کمپین با موفقیت ایجاد شد.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['targets.targetable', 'sales.product', 'sales.orderItem.order']);
        
        // Get analytics
        $analytics = $this->campaignService->getCampaignAnalytics($campaign->id);
        
        return view('admin.campaigns.show', compact('campaign', 'analytics'));
    }

    public function edit(Campaign $campaign)
    {
        $products = Product::where('is_active', true)->orderBy('title')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        $campaign->load('targets');
        
        return view('admin.campaigns.edit', compact('campaign', 'products', 'categories'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0|max:999',
            'banner_image' => 'nullable|image|max:4096',
            'badge_text' => 'nullable|string|max:50',
            'targets' => 'required|array|min:1',
            'targets.*' => 'required|string',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image
            if ($campaign->banner_image) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('campaigns', 'public');
        }

        $data['priority'] = $data['priority'] ?? 0;

        // Update campaign
        $campaign->update($data);

        // Update targets
        $campaign->targets()->delete();
        foreach ($data['targets'] as $target) {
            [$type, $id] = explode(':', $target);
            $campaign->targets()->create([
                'targetable_type' => $type === 'product' ? Product::class : Category::class,
                'targetable_id' => $id,
            ]);
        }

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'کمپین با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Campaign $campaign)
    {
        // Delete banner image
        if ($campaign->banner_image) {
            Storage::disk('public')->delete($campaign->banner_image);
        }

        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'کمپین با موفقیت حذف شد.');
    }

    public function toggleStatus(Campaign $campaign)
    {
        $campaign->update(['is_active' => !$campaign->is_active]);

        $status = $campaign->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "کمپین {$status} شد.");
    }

    public function analytics(Request $request)
    {
        $campaignId = $request->integer('campaign_id');
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');

        $campaigns = Campaign::orderBy('name')->get();
        $campaign = $campaignId ? Campaign::find($campaignId) : null;

        $analytics = null;
        if ($campaign) {
            $analytics = $this->campaignService->getCampaignAnalytics($campaign->id, $startDate, $endDate);
        }

        $globalStats = $this->campaignService->getGlobalCampaignStats($startDate, $endDate);

        return view('admin.campaigns.analytics', compact('campaigns', 'campaign', 'analytics', 'globalStats', 'startDate', 'endDate'));
    }
}