<?php

namespace App\Http\Requests\Pengawas;

use App\Models\Laporan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriaseLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengguna = $this->user();

        return $pengguna !== null && $pengguna->isAktif() && $pengguna->isPengawas();
    }

    public function rules(): array
    {
        $wajibAlasan = in_array($this->input('tindakan'), Laporan::TINDAKAN_WAJIB_ALASAN, true);

        return [
            'tindakan'        => ['required', Rule::in(array_keys(Laporan::STATUS_HASIL_TINDAKAN))],
            'kategori_triase' => ['nullable', Rule::in(array_keys(Laporan::KATEGORI_TRIASE))],
            'prioritas_triase' => ['nullable', Rule::in(array_keys(Laporan::PRIORITAS_TRIASE))],
            'catatan_publik'  => $wajibAlasan
                ? ['required', 'string', 'min:5', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
            'catatan_internal' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tindakan'        => 'keputusan',
            'kategori_triase' => 'kategori triase',
            'prioritas_triase' => 'prioritas triase',
            'catatan_publik'  => 'alasan untuk pelapor',
            'catatan_internal' => 'catatan internal',
        ];
    }

    public function messages(): array
    {
        return [
            'tindakan.required'     => 'Pilih keputusan triase terlebih dahulu.',
            'tindakan.in'           => 'Keputusan triase tidak dikenali.',
            'catatan_publik.required' => 'Alasan wajib diisi untuk tindakan meminta perbaikan atau menolak laporan.',
            'catatan_publik.min'    => 'Alasan minimal :min karakter agar pelapor memahami keputusan.',
        ];
    }
}
