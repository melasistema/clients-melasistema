<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Fits the decimal(8,2) column: at most 2 decimal places, up to 999999.99.
            'hourly_rate' => 'required|numeric|min:0|max:999999.99|decimal:0,2',
            'paid_at' => 'nullable|date',
        ];
    }
}
