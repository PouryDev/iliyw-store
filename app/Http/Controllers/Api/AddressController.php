<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'is_default' => 'boolean',
        ], [
            'title.required' => 'عنوان آدرس الزامی است',
            'title.string' => 'عنوان آدرس را به درستی وارد کنید.',
            'title.max' => 'عنوان آدرس نباید بیشتر از 255 کاراکتر باشد',
            'province.required' => 'استان الزامی است',
            'province.string' => 'استان را به درستی وارد کنید.',
            'province.max' => 'نام استان نباید بیشتر از 255 کاراکتر باشد',
            'city.required' => 'شهر الزامی است',
            'city.string' => 'شهر را به درستی وارد کنید.',
            'city.max' => 'نام شهر نباید بیشتر از 255 کاراکتر باشد',
            'address.required' => 'آدرس کامل الزامی است',
            'address.string' => 'آدرس را به درستی وارد کنید.',
            'postal_code.required' => 'کد پستی الزامی است',
            'postal_code.string' => 'کد پستی را به درستی وارد کنید.',
            'postal_code.max' => 'کد پستی نباید بیشتر از 10 رقم باشد',
            'recipient_name.required' => 'نام گیرنده الزامی است',
            'recipient_name.string' => 'نام گیرنده را به درستی وارد کنید.',
            'recipient_name.max' => 'نام گیرنده نباید بیشتر از 255 کاراکتر باشد',
            'recipient_phone.required' => 'شماره تماس گیرنده الزامی است',
            'recipient_phone.string' => 'شماره تماس گیرنده را به درستی وارد کنید.',
            'recipient_phone.max' => 'شماره تماس نباید بیشتر از 11 رقم باشد',
        ]);

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create([
            'title' => $request->title,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'province' => $request->province,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $address
        ], 201);
    }

    public function update(Request $request, Address $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'address' => 'required|string|max:1000',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'title' => $request->title,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'province' => $request->province,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت حذف شد'
        ]);
    }

    public function setDefault(Request $request, Address $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Unset other defaults
        $request->user()->addresses()->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }
}
