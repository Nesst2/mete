<?php

namespace Database\Factories;

use App\Models\Daerah;
use Illuminate\Database\Eloquent\Factories\Factory;

class DaerahFactory extends Factory
{
    protected $model = Daerah::class;

    public function definition()
    {
        return [
            'kota'     => $this->faker->city,
            'provinsi' => $this->faker->state,  // Menambahkan nilai untuk kolom provinsi
        ];
    }
}
