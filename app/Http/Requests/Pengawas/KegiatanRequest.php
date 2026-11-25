<?php

namespace App\Http\Requests\Pengawas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $diterbitkan = fn () => $this->input('aksi') === 'terbitkan';

        return [
            'aksi' => ['required', Rule::in(['draf', 'terbitkan'])],
            'kawasan_id' => [
                'required',
                'integer',
                Rule::exists('kawasan', 'kawasan_id')->where('is_aktif', true),
            ],
            'nama_kegiatan' => ['required', 'string', 'max:200'],
            'tujuan' => ['required', 'string', 'max:5000'],
            'jenis_kegiatan' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'deskripsi_target' => [Rule::requiredIf($diterbitkan), 'nullable', 'string', 'max:5000'],
            'penanggung_jawab' => [Rule::requiredIf($diterbitkan), 'nullable', 'string', 'max:200'],
            'deskripsi_sumber_daya' => ['nullable', 'string', 'max:5000'],
            'kuota_peserta' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kawasan_id' => 'kawasan',
            'nama_kegiatan' => 'nama kegiatan',
            'jenis_kegiatan' => 'jenis kegiatan',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'deskripsi_target' => 'target',
            'penanggung_jawab' => 'penanggung jawab',
            'deskripsi_sumber_daya' => 'kebutuhan sumber daya',
            'kuota_peserta' => 'kuota peserta',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'deskripsi_target.required' => 'Target wajib diisi sebelum kegiatan diterbitkan.',
            'penanggung_jawab.required' => 'Penanggung jawab wajib diisi sebelum kegiatan diterbitkan.',
            'kawasan_id.exists' => 'Kawasan yang dipilih tidak valid atau tidak aktif.',
        ];
    }
}