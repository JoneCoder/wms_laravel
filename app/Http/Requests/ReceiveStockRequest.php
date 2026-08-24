<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'location_id' => 'required|integer|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:255',
        ];
    }
}
