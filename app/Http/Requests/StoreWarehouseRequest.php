<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
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
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses')->where(function ($query) {
                    return $query->where('organization_id', auth()->user()->organization_id);
                }),
            ],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ];
    }
}
