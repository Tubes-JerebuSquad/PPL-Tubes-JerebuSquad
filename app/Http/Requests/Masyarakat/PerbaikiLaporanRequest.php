<?php

namespace App\Http\Requests\Masyarakat;

use App\Enums\KategoriLaporan;
use App\Models\Laporan;
use App\Models\Pengguna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PerbaikiLaporanRequest extends FormRequest
{
    public const MAKSIMAL_FOTO = 5;

    public function authorize(): bool
    {
        $pengguna = $this->user();
        $laporan = $this->route('laporan');

        return $pengguna instanceof Pengguna
            && $pengguna->isAktif()
            && $pengguna->isMasyarakat()
            && $laporan instanceof Laporan
            && $laporan->milikPengguna($pengguna)
            && $laporan->bisaDiubahPelapor();
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('kategori'))) {
            $this->merge([
                'kategori' => strtolower(trim($this->input('kategori'))),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'kategori' => [
                'required',
                'string',
                Rule::in(array_column(KategoriLaporan::cases(), 'value')),
            ],
            'wilayah_id' => [
                'required',
                'integer',
                Rule::exists('wilayah', 'wilayah_id')->where('is_aktif', true),
            ],
            'kawasan_id' => [
                'nullable',
                'integer',
                Rule::exists('kawasan', 'kawasan_id')->where('is_aktif', true),
            ],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'insiden_pada' => ['nullable', 'date', 'before_or_equal:now'],
            'deskripsi' => ['required', 'string', 'min:20', 'max:2000'],
            'alasan_revisi' => ['nullable', 'string', 'max:1000'],
            'pertahankan_foto' => ['nullable', 'array'],
            'pertahankan_foto.*' => ['integer'],
            'foto' => ['nullable', 'array', 'max:'.self::MAKSIMAL_FOTO],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kategori.required' => 'Kategori laporan wajib dipilih.',
            'kategori.in' => 'Kategori laporan tidak dikenal.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid atau nonaktif.',
            'kawasan_id.exists' => 'Kawasan yang dipilih tidak valid atau nonaktif.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.between' => 'Latitude harus berada pada rentang -90 sampai 90.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.between' => 'Longitude harus berada pada rentang -180 sampai 180.',
            'insiden_pada.date' => 'Format waktu kejadian tidak valid.',
            'insiden_pada.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',
            'deskripsi.required' => 'Uraian laporan wajib diisi.',
            'deskripsi.min' => 'Uraian laporan minimal 20 karakter.',
            'deskripsi.max' => 'Uraian laporan maksimal 2000 karakter.',
            'alasan_revisi.max' => 'Catatan perbaikan maksimal 1000 karakter.',
            'foto.max' => 'Jumlah foto maksimal '.self::MAKSIMAL_FOTO.' berkas.',
            'foto.*.image' => 'Setiap lampiran harus berupa berkas gambar.',
            'foto.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $jumlahDipertahankan = count(array_unique(array_filter(
                (array) $this->input('pertahankan_foto', []),
                fn ($id): bool => (int) $id > 0,
            )));
            $jumlahBaru = count($this->file('foto', []));

            if (($jumlahDipertahankan + $jumlahBaru) > self::MAKSIMAL_FOTO) {
                $validator->errors()->add(
                    'foto',
                    'Total foto dipertahankan dan foto baru maksimal '.self::MAKSIMAL_FOTO.' berkas.'
                );
            }
        });
    }
}
