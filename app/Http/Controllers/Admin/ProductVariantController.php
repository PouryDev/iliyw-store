<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Request $request)
    {
        $productId = $request->integer('product_id');
        $product = Product::findOrFail($productId);
        
        $variants = ProductVariant::where('product_id', $productId)
            ->with(['color', 'size'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.product-variants.index', compact('variants', 'product'));
    }

    public function create(Request $request)
    {
        $productId = $request->integer('product_id');
        $product = Product::findOrFail($productId);
        
        $colors = Color::where('is_active', true)->orderBy('name')->get();
        $sizes = Size::where('is_active', true)->orderBy('name')->get();

        return view('admin.product-variants.create', compact('product', 'colors', 'sizes'));
    }

    public function store(Request $request)
    {
        $productId = $request->integer('product_id');
        $product = Product::findOrFail($productId);

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'nullable|exists:colors,id',
            'size_id' => 'nullable|exists:sizes,id',
            'sku' => 'nullable|string|max:255|unique:product_variants',
            'stock' => 'required|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        ProductVariant::create($data);

        return redirect()->route('admin.product-variants.index', ['product_id' => $productId])
            ->with('success', 'تنوع محصول با موفقیت ایجاد شد.');
    }

    public function show(ProductVariant $productVariant)
    {
        $productVariant->load(['product', 'color', 'size']);
        return view('admin.product-variants.show', compact('productVariant'));
    }

    public function edit(ProductVariant $productVariant)
    {
        $productVariant->load(['product']);
        $colors = Color::where('is_active', true)->orderBy('name')->get();
        $sizes = Size::where('is_active', true)->orderBy('name')->get();

        return view('admin.product-variants.edit', compact('productVariant', 'colors', 'sizes'));
    }

    public function update(Request $request, ProductVariant $productVariant)
    {
        $data = $request->validate([
            'color_id' => 'nullable|exists:colors,id',
            'size_id' => 'nullable|exists:sizes,id',
            'sku' => 'nullable|string|max:255|unique:product_variants,sku,' . $productVariant->id,
            'stock' => 'required|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $productVariant->update($data);

        return redirect()->route('admin.product-variants.index', ['product_id' => $productVariant->product_id])
            ->with('success', 'تنوع محصول با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(ProductVariant $productVariant)
    {
        $productId = $productVariant->product_id;
        $productVariant->delete();

        return redirect()->route('admin.product-variants.index', ['product_id' => $productId])
            ->with('success', 'تنوع محصول با موفقیت حذف شد.');
    }

    public function toggleStatus(ProductVariant $productVariant)
    {
        $productVariant->update(['is_active' => !$productVariant->is_active]);

        $status = $productVariant->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "تنوع محصول {$status} شد.");
    }
}