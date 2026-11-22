<?php

namespace App\Http\Requests\Pengawas;

use App\Models\Pengguna;
use Illuminate\Foundation\Http\FormRequest;

class SimpanMateriEdukasiRequest extends FormRequest
{
    /**
     * Pastikan hanya Pengawas aktif yang dapat menyimpan atau memperbarui materi edukasi.
     */
    public function authorize(): bool
    {
        $pengguna = $this->user();

        return $pengguna instanceof Pengguna && $pengguna->isAktif() && $pengguna->isPengawas();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->aturan();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul materi wajib diisi.',
            'judul.max' => 'Judul materi maksimal 250 karakter.',
            'kategori.required' => 'Kategori materi wajib diisi.',
            'kategori.max' => 'Kategori materi maksimal 100 karakter.',
            'konten.required' => 'Isi materi wajib diisi.',
            'konten.max' => 'Isi materi terlalu panjang.',
            'referensi_sumber.required' => 'Sumber rujukan wajib diisi.',
            'referensi_sumber.max' => 'Sumber rujukan maksimal 2000 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul materi',
            'kategori' => 'kategori materi',
            'konten' => 'isi materi',
            'referensi_sumber' => 'sumber rujukan',
        ];
    }

    /**
     * Aturan validasi bersama untuk pembuatan dan pembaruan materi edukasi.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function aturan(bool $sebagian = false): array
    {
        $awalan = $sebagian ? ['sometimes'] : [];

        return [
            'judul' => [...$awalan, 'required', 'string', 'max:250'],
            'kategori' => [...$awalan, 'required', 'string', 'max:100'],
            'konten' => [...$awalan, 'required', 'string', 'max:20000'],
            'referensi_sumber' => [...$awalan, 'required', 'string', 'max:2000'],
        ];
    }
}
