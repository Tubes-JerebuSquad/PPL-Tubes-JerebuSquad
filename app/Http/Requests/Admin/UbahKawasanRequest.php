<?php

namespace App\Http\Requests\Admin;

use App\Models\Kawasan;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Kawasan $kawasan */
        $kawasan = $this->route('kawasan');

        return [
            'wilayah_id' => [
                'required',
                'integer',
                Rule::exists('wilayah', 'wilayah_id')->where(
                    fn (Builder $query) => $query->where(
                        fn (Builder $kondisi) => $kondisi
                            ->where('is_aktif', true)
                            ->orWhere('wilayah_id', $kawasan->wilayah_id)
                    )
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
