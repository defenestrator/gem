<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpeciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'species'        => ['required', 'string', 'max:255'],
            'author'         => ['nullable', 'string', 'max:255'],
            'common_name'    => ['nullable', 'string'],
            'higher_taxa'    => ['nullable', 'string', 'max:255'],
            'species_number' => ['nullable', 'string', 'max:255'],
            'changes'        => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
        ];
    }
}
