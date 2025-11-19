<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountAddressController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();
        
        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:11',
            'is_default' => 'boolean',
        ], [
            'title.required' => 'عنوان آدرس الزامی است',
            'title.max' => 'عنوان آدرس نباید بیشتر از 255 کاراکتر باشد',
            'province.required' => 'استان الزامی است',
            'province.max' => 'نام استان نباید بیشتر از 255 کاراکتر باشد',
            'city.required' => 'شهر الزامی است',
            'city.max' => 'نام شهر نباید بیشتر از 255 کاراکتر باشد',
            'address.required' => 'آدرس کامل الزامی است',
            'postal_code.required' => 'کد پستی الزامی است',
            'postal_code.max' => 'کد پستی نباید بیشتر از 10 رقم باشد',
            'recipient_name.required' => 'نام گیرنده الزامی است',
            'recipient_name.max' => 'نام گیرنده نباید بیشتر از 255 کاراکتر باشد',
            'recipient_phone.required' => 'شماره تماس گیرنده الزامی است',
            'recipient_phone.max' => 'شماره تماس نباید بیشتر از 11 رقم باشد',
        ]);

        // If this is set as default, unset all other defaults
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'آدرس با موفقیت افزوده شد'
        ]);
    }

    public function update(Request $request, Address $address)
    {
        // Check ownership
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:11',
            'is_default' => 'boolean',
        ], [
            'title.required' => 'عنوان آدرس الزامی است',
            'title.max' => 'عنوان آدرس نباید بیشتر از 255 کاراکتر باشد',
            'province.required' => 'استان الزامی است',
            'province.max' => 'نام استان نباید بیشتر از 255 کاراکتر باشد',
            'city.required' => 'شهر الزامی است',
            'city.max' => 'نام شهر نباید بیشتر از 255 کاراکتر باشد',
            'address.required' => 'آدرس کامل الزامی است',
            'postal_code.required' => 'کد پستی الزامی است',
            'postal_code.max' => 'کد پستی نباید بیشتر از 10 رقم باشد',
            'recipient_name.required' => 'نام گیرنده الزامی است',
            'recipient_name.max' => 'نام گیرنده نباید بیشتر از 255 کاراکتر باشد',
            'recipient_phone.required' => 'شماره تماس گیرنده الزامی است',
            'recipient_phone.max' => 'شماره تماس نباید بیشتر از 11 رقم باشد',
        ]);

        // If this is set as default, unset all other defaults
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($request->is_default) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'address' => $address,
            'message' => 'آدرس با موفقیت ویرایش شد'
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        // Check ownership
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت حذف شد'
        ]);
    }
}
