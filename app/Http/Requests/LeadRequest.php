<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            // Not typed by the user any more: the controller stamps the
            // channel of the page the Visit was entered from.
            'source' => ['nullable', 'string', 'in:' . implode(',', array_values(\App\Models\Lead::CHANNELS))],
            'purpose_of_visit' => ['nullable', 'string', 'max:255'],
            'service_type' => ['nullable', 'string', 'in:' . implode(',', \App\Models\Lead::SERVICE_TYPES)],
            'interested_service' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
            'expected_closing_date' => ['nullable', 'date'],
            'follow_up_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            // Photograph of the card handed over on site.
            'visiting_card_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
