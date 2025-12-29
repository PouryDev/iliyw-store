<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_address' => ['required', 'string', 'max:500'],
            'delivery_method_id' => ['required', 'exists:delivery_methods,id'],
            'delivery_address_id' => ['nullable', 'exists:addresses,id'],
            'discount_code' => ['nullable', 'string', 'exists:discount_codes,code'],
            'payment_gateway_id' => ['nullable', 'exists:payment_gateways,id'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // 10MB
        ];

        // If payment gateway is card-to-card, receipt is required
        if ($this->input('payment_gateway_id')) {
            $gateway = \App\Models\PaymentGateway::find($this->input('payment_gateway_id'));
            if ($gateway && $gateway->type === 'card_to_card') {
                $rules['receipt'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'نام الزامی است',
            'customer_phone.required' => 'شماره تلفن الزامی است',
            'customer_address.required' => 'آدرس الزامی است',
            'delivery_method_id.required' => 'روش ارسال الزامی است',
            'delivery_method_id.exists' => 'روش ارسال انتخاب شده معتبر نیست',
            'receipt.required' => 'آپلود فیش واریزی برای پرداخت کارت به کارت الزامی است',
            'receipt.file' => 'فیش واریزی باید یک فایل معتبر باشد',
            'receipt.mimes' => 'فیش واریزی باید از نوع jpg، jpeg، png یا pdf باشد',
            'receipt.max' => 'حجم فیش واریزی نباید بیشتر از 10 مگابایت باشد',
        ];
    }
}

