<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Payment::class, $this->route('project')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Fits the decimal(10,2) column: at most 2 decimal places, up to 99999999.99.
            'amount' => 'required|numeric|min:0.01|max:99999999.99|decimal:0,2',
            'paid_at' => 'required|date',
            'note' => 'nullable|string|max:255',
        ];
    }
}
