<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_name'         => 'required|string|max:255',
            'category'         => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:5000',
            'date_of_birth'    => 'nullable|date',
            'female'           => 'nullable|boolean',
            'proven_breeder'   => 'nullable|boolean',
            'acquisition_date' => 'nullable|date',
            'acquisition_cost' => 'nullable|integer|min:0',
            'status'           => 'in:draft,published',
            'images'           => 'nullable|array',
            'images.*'         => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ];
    }
}
