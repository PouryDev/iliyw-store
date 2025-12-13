<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    /**
     * Get active hero slides for frontend
     */
    public function index()
    {
        $slides = HeroSlide::active()
            ->ordered()
            ->with('linkable')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slides
        ]);
    }

    /**
     * Register a click on a hero slide
     */
    public function click($id)
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->incrementClick();

        return response()->json([
            'success' => true,
            'message' => 'کلیک ثبت شد',
            'data' => [
                'click_count' => $slide->fresh()->click_count
            ]
        ]);
    }
}

