<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * Get all active colors
     */
    public function index(): JsonResponse
    {
        $colors = Color::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $colors
        ]);
    }
}

