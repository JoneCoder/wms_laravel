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
            'product_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
            'location_id' => 'required|integer|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:255',
        ];
    }
}
