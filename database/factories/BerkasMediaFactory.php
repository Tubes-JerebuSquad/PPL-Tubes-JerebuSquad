<?php

namespace Database\Factories;

use App\Models\BerkasMedia;
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
            'nama_asli' => 'bukti-lapangan.jpg',
            'nama_file' => $namaFile,
            'path_file' => 'laporan/'.$namaFile,
            'mime_type' => 'image/jpeg',
            'ukuran_byte' => 102400,
        ];
    }
}
