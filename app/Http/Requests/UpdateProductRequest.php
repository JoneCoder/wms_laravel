<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof \App\Models\Product ? $product->id : $product;

        return [
            'sku' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('organization_id', auth()->user()->organization_id);
                })->ignore($productId),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0']
        ];
    }
}
