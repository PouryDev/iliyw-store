<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'has_variants' => ['boolean'],
            'has_colors' => ['boolean'],
            'has_sizes' => ['boolean'],
            'is_active' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png', 'max:5120'], // 5MB
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['integer', 'exists:product_images,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.color_name' => ['nullable', 'string', 'max:255'],
            'variants.*.color_hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.size_name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}

