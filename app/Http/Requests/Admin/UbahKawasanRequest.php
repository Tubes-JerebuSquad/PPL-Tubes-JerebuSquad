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
            'sumber_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('sumber_data', 'sumber_id')->where(
                    fn (Builder $query) => $query->where(
                        function (Builder $kondisi) use ($kawasan): void {
                            $kondisi->where('is_aktif', true);

                            if ($kawasan->sumber_id !== null) {
                                $kondisi->orWhere('sumber_id', $kawasan->sumber_id);
                            }
                        }
                    )
                ),
            ],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'is_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
