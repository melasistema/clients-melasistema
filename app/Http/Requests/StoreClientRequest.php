<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Unique per owner, not globally: two freelancers may share an end-client.
            'contact_email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('clients', 'contact_email')->where('user_id', $this->user()->id),
            ],
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:255',
            'unique_code' => 'nullable|string|max:255',
        ];
    }
}
