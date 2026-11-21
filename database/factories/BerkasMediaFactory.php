<?php

namespace Database\Factories;

use App\Models\BerkasMedia;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BerkasMedia>
 */
class BerkasMediaFactory extends Factory
{
    protected $model = BerkasMedia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namaFile = fake()->uuid().'.jpg';

        return [
            'diunggah_oleh' => Pengguna::factory()->masyarakat(),
            'kunci_penyimpanan' => 'laporan/'.$namaFile,
            'nama_file_asli' => 'bukti-lapangan.jpg',
            'tipe_mime' => 'image/jpeg',
            'ukuran_file_byte' => 102400,
            'checksum' => hash('sha256', $namaFile),
            'tingkat_akses' => BerkasMedia::AKSES_PRIVAT,
        ];
    }
}
