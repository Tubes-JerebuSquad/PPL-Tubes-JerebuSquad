<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SimpanPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:pengguna,email'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'peran_id' => ['required', 'integer', 'exists:peran,peran_id'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}