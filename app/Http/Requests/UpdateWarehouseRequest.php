<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
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
        $warehouse = $this->route('warehouse');
        $warehouseId = $warehouse ? $warehouse->id : null;

        return [
            'code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('warehouses')->where(function ($query) {
                    return $query->where('organization_id', auth()->user()->organization_id);
                })->ignore($warehouseId),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive']
        ];
    }
}
