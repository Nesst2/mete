<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition()
    {
        return [
            'nama' => $this->faker->company,
            // tambahkan field lain sesuai dengan struktur tabel vendor, misalnya:
            'kode_vendor' => 'V' . strtoupper($this->faker->lexify('??')) . $this->faker->numerify('####'),
            'keterangan' => $this->faker->sentence,
            'jam_operasional' => '08.00 - 17.00',
            'nomor_hp' => $this->faker->phoneNumber,
            // Field lainnya jika ada
        ];
    }
}
