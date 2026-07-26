<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|string|email|max:255|unique:clients',
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:255',
            'unique_code' => 'nullable|string|max:255',
        ];
    }
}
