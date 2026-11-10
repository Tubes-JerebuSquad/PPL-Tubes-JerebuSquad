<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pengguna = $this->route('pengguna');

        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')->ignore($pengguna->pengguna_id, 'pengguna_id'),
            ],
            'telepon' => ['nullable', 'string', 'max:30'],
            'peran_id' => ['required', 'integer', 'exists:peran,peran_id'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
