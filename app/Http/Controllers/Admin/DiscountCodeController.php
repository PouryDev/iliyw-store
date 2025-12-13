<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DiscountCodeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $discountCodes = DiscountCode::query()
            ->when($q, function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%");
            })
            ->when($status, function ($query) use ($status) {
                switch ($status) {
                    case 'active':
                        $query->where('is_active', true)
                              ->where(function ($q) {
                                  $q->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                              })
                              ->where(function ($q) {
                                  $q->whereNull('usage_limit')
                                    ->orWhereRaw('used_count < usage_limit');
                              });
                        break;
                    case 'expired':
                        $query->where(function ($q) {
                            $q->where('is_active', false)
                              ->orWhere('expires_at', '<=', now());
                        });
                        break;
                    case 'exhausted':
                        $query->where('usage_limit', '>', 0)
                              ->whereRaw('used_count >= usage_limit');
                        break;
                }
            })
            ->withCount('usages')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.discount-codes.index', compact('discountCodes', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.discount-codes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50|unique:discount_codes',
            'auto_generate' => 'boolean',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        // Validate value based on type
        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withErrors(['value' => 'درصد تخفیف نمی‌تواند بیشتر از 100 باشد.']);
        }

        // Generate code if auto_generate is true or code is empty
        if ($request->boolean('auto_generate') || empty($data['code'])) {
            $data['code'] = DiscountCode::generateCode();
        }

        // Set default values
        $data['used_count'] = 0;
        $data['is_active'] = $data['is_active'] ?? true;

        DiscountCode::create($data);

        return redirect()->route('admin.discount-codes.index')
            ->with('success', 'کد تخفیف با موفقیت ایجاد شد.');
    }

    public function show(DiscountCode $discountCode)
    {
        $discountCode->load(['usages.user', 'usages.order']);
        return view('admin.discount-codes.show', compact('discountCode'));
    }

    public function edit(DiscountCode $discountCode)
    {
        return view('admin.discount-codes.edit', compact('discountCode'));
    }

    public function update(Request $request, DiscountCode $discountCode)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discount_codes,code,' . $discountCode->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        // Validate value based on type
        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withErrors(['value' => 'درصد تخفیف نمی‌تواند بیشتر از 100 باشد.']);
        }

        $discountCode->update($data);

        return redirect()->route('admin.discount-codes.index')
            ->with('success', 'کد تخفیف با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(DiscountCode $discountCode)
    {
        // Check if code has been used
        if ($discountCode->used_count > 0) {
            return back()->withErrors(['error' => 'نمی‌توان کد تخفیفی که استفاده شده است را حذف کرد.']);
        }

        $discountCode->delete();

        return redirect()->route('admin.discount-codes.index')
            ->with('success', 'کد تخفیف با موفقیت حذف شد.');
    }

    public function toggleStatus(DiscountCode $discountCode)
    {
        $discountCode->update(['is_active' => !$discountCode->is_active]);

        $status = $discountCode->is_active ? 'فعال' : 'غیرفعال';
        return back()->with('success', "کد تخفیف {$status} شد.");
    }
}