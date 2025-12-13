<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $colors = Color::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->withCount('productVariants')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.colors.index', compact('colors', 'q'));
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:colors',
            'hex_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        Color::create($data);

        return redirect()->route('admin.colors.index')
            ->with('success', 'رنگ با موفقیت ایجاد شد.');
    }

    public function show(Color $color)
    {
        $color->load('productVariants.product');
        return view('admin.colors.show', compact('color'));
    }

    public function edit(Color $color)
    {
        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'hex_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $color->update($data);

        return redirect()->route('admin.colors.index')
            ->with('success', 'رنگ با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Color $color)
    {
        // Check if color has variants
        if ($color->productVariants()->count() > 0) {
            return back()->withErrors(['error' => 'نمی‌توان رنگی که در محصولات استفاده شده است را حذف کرد.']);
        }

        $color->delete();

        return redirect()->route('admin.colors.index')
            ->with('success', 'رنگ با موفقیت حذف شد.');
    }

    public function toggleStatus(Color $color)
    {
        $color->update(['is_active' => !$color->is_active]);

        $status = $color->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "رنگ {$status} شد.");
    }
}