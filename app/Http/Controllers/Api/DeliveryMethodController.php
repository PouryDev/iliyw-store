<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Models\DeliveryMethod;
use Illuminate\Http\JsonResponse;

class DeliveryMethodController extends Controller
{
    /**
     * Get all active delivery methods (public)
     */
    public function index(): JsonResponse
    {
        $deliveryMethods = DeliveryMethod::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => DeliveryMethodResource::collection($deliveryMethods)
        ]);
    }
}
