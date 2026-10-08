<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mergeData = [];

        // Empty number inputs post as "" which is not nullable — normalise to null
        foreach ([
            'plants_total', 'materials_total', 'labour_total', 'transport_total',
            'other_total', 'profit_margin_percent', 'discount_amount', 'tax_percent',
        ] as $field) {
            if ($this->input($field) === '') {
                $mergeData[$field] = null;
            }
        }

        // Components arrive as JSON (same pattern as QuotationRequest)
        $items = $this->input('items', []);
        if (empty($items) && $this->filled('items_json')) {
            $decoded = json_decode($this->input('items_json'), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (is_array($items)) {
            $normalized = [];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalized[] = [
                    'item_type' => $item['item_type'] ?? 'Other',
                    'item_name' => trim((string) ($item['item_name'] ?? '')),
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit' => $item['unit'] ?? 'Nos',
                    'rate' => (float) ($item['rate'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                ];
            }
            $mergeData['items'] = $normalized;
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],

            'plants_total' => ['nullable', 'numeric', 'min:0'],
            'materials_total' => ['nullable', 'numeric', 'min:0'],
            'labour_total' => ['nullable', 'numeric', 'min:0'],
            'transport_total' => ['nullable', 'numeric', 'min:0'],
            'other_total' => ['nullable', 'numeric', 'min:0'],

            'profit_margin_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],

            // Cost components — names/prices are free-form and differ per estimation
            'items' => ['nullable', 'array'],
            'items.*.item_type' => ['required', 'string', Rule::in(['Plant', 'Material', 'Labour', 'Transport', 'Other'])],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.item_name.required' => 'Every component needs a name.',
            'items.*.quantity.required' => 'Every component needs a quantity.',
            'items.*.rate.required' => 'Every component needs a rate.',
        ];
    }
}
