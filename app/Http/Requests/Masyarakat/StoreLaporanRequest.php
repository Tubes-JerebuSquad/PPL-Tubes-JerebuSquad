<?php

namespace App\Http\Requests\Masyarakat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaporanRequest extends FormRequest
{
    public const KATEGORI = [
        'deforestasi' => 'Deforestasi / penebangan liar',
        'kebakaran' => 'Kebakaran hutan dan lahan',
        'alih_fungsi_lahan' => 'Alih fungsi lahan',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isMasyarakat();
    }

    public function rules(): array
    {
        return [
            'kategori' => ['required', Rule::in(array_keys(self::KATEGORI))],
            'deskripsi' => ['required', 'string', 'min:20', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'insiden_pada' => ['required', 'date', 'before_or_equal:now'],
            'foto' => ['required', 'array', 'min:1', 'max:5'],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:51200'],
            'wilayah_id' => ['required', 'integer', Rule::exists('wilayah', 'wilayah_id') 
                ->where(fn ($query) => $query->where('is_aktif', true)),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'kategori' => 'kategori',
            'deskripsi' => 'deskripsi',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'insiden_pada' => 'waktu kejadian',
            'foto' => 'foto',
            'foto.*' => 'foto',
            'video' => 'video',
            'wilayah_id' => 'wilayah',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'latitude.required' => 'Lokasi belum diisi. Tekan "Ambil lokasi saya" atau isi koordinat manual.',
            'longitude.required' => 'Lokasi belum diisi. Tekan "Ambil lokasi saya" atau isi koordinat manual.',
            'deskripsi.min' => 'Deskripsi minimal :min karakter.',
            'deskripsi.max' => 'Deskripsi maksimal :max karakter.',
            'insiden_pada.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',
            'foto.min' => 'Minimal unggah 1 foto.',
            'foto.max' => 'Maksimal 5 foto.',
            'foto.*.image' => 'Berkas foto harus berupa gambar.',
            'foto.*.mimes' => 'Foto harus berformat JPG atau PNG.',
            'foto.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
            'video.mimetypes' => 'Video harus berformat MP4 atau MOV.',
            'video.max' => 'Ukuran video maksimal 50 MB.',
        ];
    }
}