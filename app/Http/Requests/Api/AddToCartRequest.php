<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'size_id' => ['nullable', 'exists:sizes,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'تعداد الزامی است',
            'quantity.min' => 'تعداد باید حداقل 1 باشد',
            'color_id.exists' => 'رنگ انتخاب شده معتبر نیست',
            'size_id.exists' => 'سایز انتخاب شده معتبر نیست',
        ];
    }
}

