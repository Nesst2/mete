<?php

namespace Database\Factories;

use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TagihanFactory extends Factory
{
    protected $model = Tagihan::class;

    public function definition()
    {
        return [
            'vendor_id'       => 1, // Pastikan atau ganti dengan cara assign vendor secara dinamis jika diperlukan
            'kunjungan_ke'    => $this->faker->numberBetween(1, 15),
            'status_kunjungan'=> $this->faker->randomElement(['ada orang', 'tertunda', 'masih', 'tidak ada orang']),
            'uang_masuk'      => $this->faker->numberBetween(0, 50000),
            'tanggal_masuk'   => Carbon::now(),
            'daerah_id'       => 1, // Sesuaikan jika diperlukan
        ];
    }
}
