<?php

namespace App\Http\Requests\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wilayah_id' => [
                'required',
                'integer',
                Rule::exists('wilayah', 'wilayah_id')->where(
                    fn (Builder $query) => $query->where('is_aktif', true)
                ),
            ],
            'nama_kawasan' => ['required', 'string', 'max:200'],
            'jenis_kawasan' => ['required', 'string', 'max:100'],
            'status_kawasan' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'is_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
