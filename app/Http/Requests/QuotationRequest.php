<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mergeData = [];

        // Normalize salesperson
        if ($this->has('salesperson_id') && !$this->has('sales_person_id')) {
            $mergeData['sales_person_id'] = $this->input('salesperson_id');
        }

        // Normalize discount
        if ($this->has('discount_amount') && !$this->has('discount_value')) {
            $mergeData['discount_value'] = $this->input('discount_amount');
        }
        if ($this->input('discount_type') === 'percent') {
            $mergeData['discount_type'] = 'percentage';
        }

        // Normalize terms
        if ($this->has('terms') && !$this->has('terms_conditions')) {
            $mergeData['terms_conditions'] = $this->input('terms');
        }

        // Normalize items array
        $items = $this->input('items', []);
        if (empty($items) && $this->filled('items_json')) {
            $decoded = json_decode($this->input('items_json'), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (is_array($items)) {
            $normalizedItems = [];
            foreach ($items as $key => $item) {
                if (!is_array($item)) continue;

                $normalizedItems[$key] = [
                    'item_type' => $item['item_type'] ?? $item['category'] ?? 'Plant',
                    'item_name' => $item['item_name'] ?? $item['description'] ?? 'Item',
                    'description' => $item['description'] ?? $item['item_name'] ?? null,
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit' => $item['unit'] ?? 'Nos',
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'discount' => (float) ($item['discount'] ?? $item['discount_percent'] ?? 0),
                    'tax_percent' => (float) ($item['tax_percent'] ?? 18),
                ];
            }
            $mergeData['items'] = $normalizedItems;
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
            'sales_person_id' => ['nullable', 'exists:users,id'],
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'terms_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'string'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
