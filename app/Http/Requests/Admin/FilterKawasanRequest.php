<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class FilterKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (! $this->spasialTersedia()) {
            return [];
        }

        return [
            'min_lon' => ['nullable', 'numeric', 'between:-180,180'],
            'min_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'max_lon' => ['nullable', 'numeric', 'between:-180,180'],
            'max_lat' => ['nullable', 'numeric', 'between:-90,90'],
        ];
    }

    /**
     * Keempat koordinat harus diisi bersamaan dan membentuk area yang valid.
     */
    public function after(): array
    {
        if (! $this->spasialTersedia()) {
            return [];
        }

        return [
            function (Validator $validator): void {
                $kunci = ['min_lon', 'min_lat', 'max_lon', 'max_lat'];

                $terisi = array_values(array_filter(
                    $kunci,
                    fn (string $kolom): bool => filled($this->input($kolom))
                ));

                if ($terisi === []) {
                    return;
                }

                if (count($terisi) !== count($kunci)) {
                    $validator->errors()->add(
                        'bbox',
                        'Lengkapi keempat koordinat area: bujur minimum, lintang minimum, bujur maksimum, dan lintang maksimum.'
                    );

                    return;
                }

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ((float) $this->input('min_lon') >= (float) $this->input('max_lon')) {
                    $validator->errors()->add(
                        'max_lon',
                        'Bujur maksimum harus lebih besar dari bujur minimum.'
                    );
                }

                if ((float) $this->input('min_lat') >= (float) $this->input('max_lat')) {
                    $validator->errors()->add(
                        'max_lat',
                        'Lintang maksimum harus lebih besar dari lintang minimum.'
                    );
                }
            },
        ];
    }

    /**
     * Mengambil area koordinat bila keempat nilai valid dan lengkap.
     *
     * @return array{min_lon: float, min_lat: float, max_lon: float, max_lat: float}|null
     */
    public function bbox(): ?array
    {
        $data = $this->validated();

        $kunci = ['min_lon', 'min_lat', 'max_lon', 'max_lat'];

        foreach ($kunci as $kolom) {
            if (! isset($data[$kolom])) {
                return null;
            }
        }

        return [
            'min_lon' => (float) $data['min_lon'],
            'min_lat' => (float) $data['min_lat'],
            'max_lon' => (float) $data['max_lon'],
            'max_lat' => (float) $data['max_lat'],
        ];
    }

    /**
     * Filter spasial hanya didukung pada PostgreSQL/PostGIS. Pada driver lain
     * parameter bounding box diabaikan sepenuhnya.
     */
    private function spasialTersedia(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }
}
