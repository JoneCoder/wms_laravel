<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
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
        $warehouseId = $warehouse instanceof \App\Models\Warehouse ? $warehouse->id : $warehouse;

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations')->where(function ($query) use ($warehouseId) {
                    return $query->where('warehouse_id', $warehouseId);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:bin,rack,shelf'],
            'status' => ['nullable', 'in:active,inactive']
        ];
    }
}
