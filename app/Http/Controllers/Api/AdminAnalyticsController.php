<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Campaign;
use App\Models\CampaignSale;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Get overall analytics stats
     */
    public function index(Request $request)
    {
        $filters = $this->getFilters($request);
        $cacheKey = 'analytics:overview:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Order::query();

            // Apply date filters
            if ($filters['start_date']) {
                $query->where('created_at', '>=', $filters['start_date']);
            }
            if ($filters['end_date']) {
                $query->where('created_at', '<=', $filters['end_date']->endOfDay());
            }
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }

            $totalOrders = $query->count();
            $totalRevenue = (clone $query)->sum('final_amount');
            $totalItems = (clone $query)->withCount('items')->get()->sum('items_count');
            $averageOrder = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

            // Orders by status
            $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_items' => $totalItems,
                'average_order' => $averageOrder,
                'orders_by_status' => $ordersByStatus,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get sales by day
     */
    public function salesByDay(Request $request)
    {
        $filters = $this->getFilters($request);
        $cacheKey = 'analytics:sales-by-day:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('AVG(final_amount) as average_order')
            )
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->when($filters['status'], function ($q) use ($filters) {
                    $q->where('status', $filters['status']);
                })
                ->groupBy('date')
                ->orderBy('date', 'asc');

            return $query->get()->map(function ($item) {
                return [
                    'date' => $item->date,
                    'orders_count' => (int) $item->orders_count,
                    'revenue' => (int) $item->revenue,
                    'average_order' => (int) round($item->average_order),
                ];
            });
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get sales by hour
     */
    public function salesByHour(Request $request)
    {
        $filters = $this->getFilters($request);
        $cacheKey = 'analytics:sales-by-hour:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(final_amount) as revenue')
            )
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->when($filters['status'], function ($q) use ($filters) {
                    $q->where('status', $filters['status']);
                })
                ->groupBy('date', 'hour')
                ->orderBy('date', 'asc')
                ->orderBy('hour', 'asc');

            $results = $query->get();

            // Group by date and create hourly breakdown
            $grouped = $results->groupBy('date')->map(function ($dayData, $date) {
                $hourlyData = [];
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourData = $dayData->firstWhere('hour', $hour);
                    $hourlyData[] = [
                        'hour' => $hour,
                        'orders_count' => $hourData ? (int) $hourData->orders_count : 0,
                        'revenue' => $hourData ? (int) $hourData->revenue : 0,
                    ];
                }
                return [
                    'date' => $date,
                    'total_orders' => $dayData->sum('orders_count'),
                    'total_revenue' => $dayData->sum('revenue'),
                    'hourly' => $hourlyData,
                ];
            });

            return $grouped->values();
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request)
    {
        $filters = $this->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = 'analytics:top-products:' . md5(serialize($filters) . $limit);

        $data = Cache::remember($cacheKey, 3600, function () use ($filters, $limit) {
            $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.line_total) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders_count')
                )
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('orders.created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('orders.created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->when($filters['status'], function ($q) use ($filters) {
                    $q->where('orders.status', $filters['status']);
                })
                ->groupBy('products.id', 'products.title', 'products.slug')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit);

            return $query->get()->map(function ($item, $index) {
                return [
                    'rank' => $index + 1,
                    'product_id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (int) $item->total_revenue,
                    'orders_count' => (int) $item->orders_count,
                ];
            });
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get top selling categories
     */
    public function topCategories(Request $request)
    {
        $filters = $this->getFilters($request);
        $limit = $request->input('limit', 10);
        $cacheKey = 'analytics:top-categories:' . md5(serialize($filters) . $limit);

        $data = Cache::remember($cacheKey, 3600, function () use ($filters, $limit) {
            $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'categories.id',
                    'categories.name',
                    'categories.slug',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.line_total) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
                    DB::raw('COUNT(DISTINCT products.id) as products_count')
                )
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('orders.created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('orders.created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->when($filters['status'], function ($q) use ($filters) {
                    $q->where('orders.status', $filters['status']);
                })
                ->groupBy('categories.id', 'categories.name', 'categories.slug')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit);

            return $query->get()->map(function ($item, $index) {
                return [
                    'rank' => $index + 1,
                    'category_id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (int) $item->total_revenue,
                    'orders_count' => (int) $item->orders_count,
                    'products_count' => (int) $item->products_count,
                ];
            });
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get campaign statistics
     */
    public function campaigns(Request $request)
    {
        $filters = $this->getFilters($request);
        $cacheKey = 'analytics:campaigns:' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = CampaignSale::join('campaigns', 'campaign_sales.campaign_id', '=', 'campaigns.id')
                ->join('order_items', 'campaign_sales.order_item_id', '=', 'order_items.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    'campaigns.id',
                    'campaigns.name',
                    'campaigns.type',
                    'campaigns.discount_value',
                    DB::raw('SUM(campaign_sales.quantity) as total_quantity'),
                    DB::raw('SUM(campaign_sales.total_discount) as total_discount'),
                    DB::raw('SUM(campaign_sales.final_price * campaign_sales.quantity) as total_revenue'),
                    DB::raw('COUNT(DISTINCT campaign_sales.order_item_id) as sales_count')
                )
                ->when($filters['start_date'], function ($q) use ($filters) {
                    $q->where('campaign_sales.created_at', '>=', $filters['start_date']);
                })
                ->when($filters['end_date'], function ($q) use ($filters) {
                    $q->where('campaign_sales.created_at', '<=', $filters['end_date']->endOfDay());
                })
                ->when($filters['status'], function ($q) use ($filters) {
                    $q->where('orders.status', $filters['status']);
                })
                ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.type', 'campaigns.discount_value')
                ->orderBy('total_revenue', 'desc');

            return $query->get()->map(function ($item) {
                return [
                    'campaign_id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'discount_value' => $item->discount_value,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_discount' => (int) $item->total_discount,
                    'total_revenue' => (int) $item->total_revenue,
                    'sales_count' => (int) $item->sales_count,
                ];
            });
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get hero slides statistics
     */
    public function heroSlides(Request $request)
    {
        $cacheKey = 'analytics:hero-slides';

        $data = Cache::remember($cacheKey, 3600, function () {
            $slides = HeroSlide::with('linkable')->get();

            $stats = [
                'total_slides' => $slides->count(),
                'active_slides' => $slides->where('is_active', true)->count(),
                'total_clicks' => $slides->sum('click_count'),
                'slides_by_link_type' => $slides->groupBy('link_type')->map(function ($group, $type) {
                    return [
                        'type' => $type,
                        'count' => $group->count(),
                        'total_clicks' => $group->sum('click_count'),
                        'slides' => $group->map(function ($slide) {
                            $linkInfo = null;
                            if ($slide->linkable) {
                                if ($slide->link_type === 'product') {
                                    $linkInfo = [
                                        'type' => 'product',
                                        'id' => $slide->linkable->id,
                                        'title' => $slide->linkable->title,
                                    ];
                                } elseif ($slide->link_type === 'category') {
                                    $linkInfo = [
                                        'type' => 'category',
                                        'id' => $slide->linkable->id,
                                        'name' => $slide->linkable->name,
                                    ];
                                } elseif ($slide->link_type === 'campaign') {
                                    $linkInfo = [
                                        'type' => 'campaign',
                                        'id' => $slide->linkable->id,
                                        'name' => $slide->linkable->name,
                                    ];
                                }
                            }

                            return [
                                'id' => $slide->id,
                                'title' => $slide->title,
                                'link_type' => $slide->link_type,
                                'click_count' => $slide->click_count,
                                'is_active' => $slide->is_active,
                                'link_info' => $linkInfo,
                                'custom_url' => $slide->custom_url,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];

            return $stats;
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Parse and normalize filters from request
     */
    private function getFilters(Request $request): array
    {
        $dateRange = $request->input('date_range');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        $start = null;
        $end = null;

        if ($dateRange) {
            switch ($dateRange) {
                case 'today':
                    $start = Carbon::today();
                    $end = Carbon::today();
                    break;
                case 'week':
                    $start = Carbon::now()->startOfWeek();
                    $end = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $start = Carbon::now()->startOfYear();
                    $end = Carbon::now()->endOfYear();
                    break;
            }
        }

        if ($startDate) {
            $start = Carbon::parse($startDate);
        }
        if ($endDate) {
            $end = Carbon::parse($endDate);
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'status' => $status,
        ];
    }
}
