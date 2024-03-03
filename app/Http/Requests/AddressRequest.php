<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'province_name' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'string', 'max:255'],
            'city_name' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'string', 'max:255'],
            'district_name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'string', 'max:255'],
            'postal_code_name' => ['required', 'string', 'max:255'],
            'postal_code_id' => ['required', 'string', 'max:255'],
            'address_category_id' => ['required', 'exists:address_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'detail' => ['required', 'string', 'max:255'],
            'additional' => ['nullable', 'string', 'max:255'],
        ];
    }
}
