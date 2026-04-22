<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_name'         => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:animals,slug|alpha_dash',
            'description'      => 'nullable|string|max:5000',
            'date_of_birth'    => 'nullable|date',
            'female'           => 'nullable|boolean',
            'proven_breeder'   => 'nullable|boolean',
            'acquisition_date' => 'nullable|date',
            'acquisition_cost' => 'nullable|integer|min:0',
            'status'           => 'in:draft,published',
            'availability'     => 'nullable|in:for_sale,breeder,sold,not_for_sale',
            'images'           => 'nullable|array',
            'images.*'         => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ];
    }
}
