<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\Category;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminHeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::with('linkable')
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slides
        ]);
    }

    public function show($id)
    {
        $slide = HeroSlide::with('linkable')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $slide
        ]);
    }

    public function store(Request $request)
    {
        // Convert empty strings to null for proper validation
        $request->merge([
            'linkable_id' => $request->input('linkable_id') === '' || $request->input('linkable_id') === null ? null : $request->input('linkable_id'),
            'custom_url' => $request->input('custom_url') === '' || $request->input('custom_url') === null ? null : $request->input('custom_url'),
        ]);

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:5120', // 5MB max
            'link_type' => 'required|in:product,category,campaign,custom',
            'linkable_id' => 'nullable|integer|required_if:link_type,product,category,campaign',
            'custom_url' => 'nullable|string|max:500|required_if:link_type,custom',
            'button_text' => 'nullable|string|max:100',
            'is_active' => 'nullable',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('hero-slides', 'public');
            $data['image_path'] = $path;
        }

        // Handle polymorphic relationship
        if (in_array($data['link_type'], ['product', 'category', 'campaign'])) {
            $modelClass = null;
            switch ($data['link_type']) {
                case 'product':
                    $modelClass = Product::class;
                    break;
                case 'category':
                    $modelClass = Category::class;
                    break;
                case 'campaign':
                    $modelClass = Campaign::class;
                    break;
            }

            if ($modelClass) {
                $linkable = $modelClass::findOrFail($data['linkable_id']);
                $data['linkable_type'] = get_class($linkable);
                $data['linkable_id'] = $linkable->id;
            }
        } else {
            $data['linkable_type'] = null;
            $data['linkable_id'] = null;
        }

        // Set defaults
        $data['click_count'] = 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? HeroSlide::max('sort_order') + 1;

        $slide = HeroSlide::create($data);

        return response()->json([
            'success' => true,
            'data' => $slide->load('linkable'),
            'message' => 'اسلاید با موفقیت ایجاد شد'
        ]);
    }

    public function update(Request $request, $id)
    {
        $slide = HeroSlide::findOrFail($id);

        // Convert empty strings to null for proper validation
        $linkableId = $request->input('linkable_id');
        $customUrl = $request->input('custom_url');
        $linkType = $request->input('link_type');
        
        $request->merge([
            'linkable_id' => ($linkableId === '' || $linkableId === null) ? null : $linkableId,
            'custom_url' => ($customUrl === '' || $customUrl === null) ? null : $customUrl,
            // Don't convert link_type to null if it's empty - let validation handle it
            // 'link_type' should always be present and valid
        ]);

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'sometimes|image|max:5120',
            'link_type' => 'required|in:product,category,campaign,custom',
            'linkable_id' => 'nullable|integer|required_if:link_type,product,category,campaign',
            'custom_url' => 'nullable|string|max:500|required_if:link_type,custom',
            'button_text' => 'nullable|string|max:100',
            'is_active' => 'nullable',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($slide->image_path && !str_starts_with($slide->image_path, 'http')) {
                Storage::disk('public')->delete($slide->image_path);
            }

            $path = $request->file('image')->store('hero-slides', 'public');
            $data['image_path'] = $path;
        }

        // Handle polymorphic relationship
        if (in_array($data['link_type'], ['product', 'category', 'campaign'])) {
            $modelClass = null;
            switch ($data['link_type']) {
                case 'product':
                    $modelClass = Product::class;
                    break;
                case 'category':
                    $modelClass = Category::class;
                    break;
                case 'campaign':
                    $modelClass = Campaign::class;
                    break;
            }

            if ($modelClass) {
                $linkable = $modelClass::findOrFail($data['linkable_id']);
                $data['linkable_type'] = get_class($linkable);
                $data['linkable_id'] = $linkable->id;
            }
        } else {
            $data['linkable_type'] = null;
            $data['linkable_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', $slide->is_active);

        $slide->update($data);

        return response()->json([
            'success' => true,
            'data' => $slide->load('linkable'),
            'message' => 'اسلاید با موفقیت به‌روزرسانی شد'
        ]);
    }

    public function destroy($id)
    {
        $slide = HeroSlide::findOrFail($id);

        // Delete image from storage
        if ($slide->image_path && !str_starts_with($slide->image_path, 'http')) {
            Storage::disk('public')->delete($slide->image_path);
        }

        $slide->delete();

        return response()->json([
            'success' => true,
            'message' => 'اسلاید با موفقیت حذف شد'
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'slides' => 'required|array',
            'slides.*.id' => 'required|exists:hero_slides,id',
            'slides.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('slides') as $slideData) {
            HeroSlide::where('id', $slideData['id'])
                ->update(['sort_order' => $slideData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'ترتیب اسلایدها با موفقیت به‌روزرسانی شد'
        ]);
    }
}

