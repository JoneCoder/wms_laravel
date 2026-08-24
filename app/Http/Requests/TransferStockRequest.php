<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'source_location_id' => 'required|integer|exists:locations,id',
            'destination_location_id' => 'required|integer|exists:locations,id|different:source_location_id',
            'quantity' => 'required|integer|min:1',
        ];
    }
}
