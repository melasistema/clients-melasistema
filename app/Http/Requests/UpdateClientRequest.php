<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('client'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('clients', 'contact_email')->ignore($this->route('client')->id),
            ],
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:255',
            'unique_code' => 'nullable|string|max:255',
        ];
    }
}
