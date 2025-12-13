<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $sizes = Size::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->withCount('productVariants')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.sizes.index', compact('sizes', 'q'));
    }

    public function create()
    {
        return view('admin.sizes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:sizes',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        Size::create($data);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'سایز با موفقیت ایجاد شد.');
    }

    public function show(Size $size)
    {
        $size->load('productVariants.product');
        return view('admin.sizes.show', compact('size'));
    }

    public function edit(Size $size)
    {
        return view('admin.sizes.edit', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name,' . $size->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $size->update($data);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'سایز با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Size $size)
    {
        // Check if size has variants
        if ($size->productVariants()->count() > 0) {
            return back()->withErrors(['error' => 'نمی‌توان سایزی که در محصولات استفاده شده است را حذف کرد.']);
        }

        $size->delete();

        return redirect()->route('admin.sizes.index')
            ->with('success', 'سایز با موفقیت حذف شد.');
    }

    public function toggleStatus(Size $size)
    {
        $size->update(['is_active' => !$size->is_active]);

        $status = $size->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "سایز {$status} شد.");
    }
}