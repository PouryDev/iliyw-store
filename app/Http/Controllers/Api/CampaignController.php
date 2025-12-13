<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function active()
    {
        $campaigns = Campaign::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns
        ]);
    }
}
