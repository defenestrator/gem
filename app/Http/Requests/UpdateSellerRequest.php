<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'email'          => ['nullable', 'email', 'max:140'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'website'        => ['nullable', 'url', 'max:255'],
            'instagram'      => ['nullable', 'string', 'max:255'],
            'youtube'        => ['nullable', 'url', 'max:255'],
            'facebook'       => ['nullable', 'url', 'max:255'],
            'morph_market'   => ['nullable', 'url', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'unit_number'    => ['nullable', 'string', 'max:50'],
            'city'           => ['nullable', 'string', 'max:100'],
            'state'          => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'country'        => ['nullable', 'string', 'max:100'],
        ];
    }
}
