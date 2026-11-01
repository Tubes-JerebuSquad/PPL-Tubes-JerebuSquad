<?php

namespace App\Http\Requests\Katalog;

use App\Enums\StatusKualitas;
use App\Models\SumberData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanSumberDataRequest extends FormRequest
{
    /**
     * Pola URL yang memuat kredensial (user:pass@host) sehingga tidak boleh disimpan.
     */
    private const POLA_KREDENSIAL_URL = '/^[a-z][a-z0-9+.\-]*:\/\/[^\/?#@]*@/i';

    /**
     * Otorisasi katalog memakai Policy yang menyusul bersama fitur autentikasi.
     */
    public function authorize(): bool
    {
        return true;
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
            'nama_sumber.required' => 'Nama sumber wajib diisi.',
            'nama_sumber.unique' => 'Nama sumber sudah dipakai dataset lain.',
            'jenis_sumber.required' => 'Jenis sumber wajib dipilih.',
            'jenis_sumber.in' => 'Jenis sumber harus INTERNAL atau EKSTERNAL.',
            'nama_penyedia.required' => 'Nama penyedia wajib diisi.',
            'url_sumber.url' => 'URL sumber harus berupa alamat http atau https yang valid.',
            'url_sumber.not_regex' => 'URL sumber tidak boleh memuat kredensial (user:pass@).',
            'nama_lisensi.required' => 'Nama lisensi wajib diisi.',
            'format_data.required' => 'Format data wajib diisi.',
            'periode_selesai.after_or_equal' => 'Periode selesai tidak boleh sebelum periode mulai.',
            'kode_crs.required' => 'Kode CRS wajib diisi.',
            'kode_crs.regex' => 'Kode CRS harus berformat EPSG diikuti 4-6 digit, contoh EPSG:4326.',
            'interval_penyegaran_jam.min' => 'Interval penyegaran minimal 1 jam.',
            'status_kualitas.required' => 'Status kualitas wajib dipilih.',
            'status_kualitas.enum' => 'Status kualitas tidak dikenal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_sumber' => 'nama sumber',
            'jenis_sumber' => 'jenis sumber',
            'nama_penyedia' => 'nama penyedia',
            'url_sumber' => 'URL sumber',
            'nama_lisensi' => 'nama lisensi',
            'format_data' => 'format data',
            'deskripsi_cakupan' => 'deskripsi cakupan',
            'periode_mulai' => 'periode mulai',
            'periode_selesai' => 'periode selesai',
            'kode_crs' => 'kode CRS',
            'interval_penyegaran_jam' => 'interval penyegaran',
            'status_kualitas' => 'status kualitas',
        ];
    }

    /**
     * Aturan metadata yang dipakai bersama oleh penyimpanan dan pembaruan.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function aturan(bool $sebagian = false): array
    {
        $sumber = $this->sumberTersimpan();
        $awalan = $sebagian ? ['sometimes'] : [];

        $aturanNama = [...$awalan, 'required', 'string', 'max:200'];
        $unik = Rule::unique('sumber_data', 'nama_sumber');

        $aturanNama[] = $sumber instanceof SumberData
            ? $unik->ignore($sumber->sumber_id, 'sumber_id')
            : $unik;

        $aturanPeriodeSelesai = [...$awalan, 'nullable', 'date'];
        $batasPeriode = $this->batasPeriodeSelesai();

        if ($batasPeriode !== null) {
            $aturanPeriodeSelesai[] = 'after_or_equal:'.$batasPeriode;
        }

        return [
            'nama_sumber' => $aturanNama,
            'jenis_sumber' => [
                ...$awalan,
                'required',
                'string',
                Rule::in([SumberData::JENIS_INTERNAL, SumberData::JENIS_EKSTERNAL]),
            ],
            'nama_penyedia' => [...$awalan, 'required', 'string', 'max:200'],
            'url_sumber' => [
                ...$awalan,
                'nullable',
                'string',
                'max:2048',
                'url:http,https',
                'not_regex:'.self::POLA_KREDENSIAL_URL,
            ],
            'nama_lisensi' => [...$awalan, 'required', 'string', 'max:200'],
            'format_data' => [...$awalan, 'required', 'string', 'max:50'],
            'deskripsi_cakupan' => [...$awalan, 'nullable', 'string', 'max:2000'],
            'periode_mulai' => [...$awalan, 'nullable', 'date'],
            'periode_selesai' => $aturanPeriodeSelesai,
            'kode_crs' => [...$awalan, 'required', 'string', 'max:50', 'regex:/^EPSG:\d{4,6}$/i'],
            'interval_penyegaran_jam' => [...$awalan, 'nullable', 'integer', 'min:1', 'max:8760'],
            'status_kualitas' => [...$awalan, 'required', Rule::enum(StatusKualitas::class)],
        ];
    }

    protected function sumberTersimpan(): ?SumberData
    {
        $sumber = $this->route('sumberData');

        if ($sumber instanceof SumberData) {
            return $sumber;
        }

        if (is_string($sumber) || is_int($sumber)) {
            return SumberData::find($sumber);
        }

        return null;
    }

    /**
     * Batas bawah periode selesai: nilai pada payload, atau nilai tersimpan saat pembaruan.
     */
    protected function batasPeriodeSelesai(): ?string
    {
        $nilai = $this->has('periode_mulai')
            ? $this->input('periode_mulai')
            : $this->sumberTersimpan()?->periode_mulai?->format('Y-m-d');

        if (! is_string($nilai) || trim($nilai) === '') {
            return null;
        }

        return $nilai;
    }
}
