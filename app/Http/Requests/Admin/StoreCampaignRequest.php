<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Normalize frontend aliases to database column names.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->input('name') ?? $this->input('title'),
            'type' => $this->input('type') ?? $this->input('discount_type'),
            'ends_at' => $this->input('ends_at') ?? $this->input('expires_at'),
            'starts_at' => $this->input('starts_at') ?: now()->toDateTimeString(),
            'is_active' => $this->boolean('is_active', true),
            'priority' => $this->input('priority', 0),
        ]);

        // ends_at is required by DB; default to 1 year if missing
        if (!$this->filled('ends_at')) {
            $this->merge([
                'ends_at' => now()->addYear()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'integer', 'min:0'],
            'max_discount_amount' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['exists:products,id'],
        ];
    }

    /**
     * Fields that map to the campaigns table.
     */
    public function campaignAttributes(): array
    {
        return $this->safe()->only([
            'name',
            'description',
            'type',
            'discount_value',
            'max_discount_amount',
            'starts_at',
            'ends_at',
            'priority',
            'is_active',
        ]);
    }
}
