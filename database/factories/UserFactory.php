<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    
    public function definition(): array
    {
        return [
            'nama'          => $this->faker->name(),
            'tanggal_lahir' => $this->faker->date(),
            'nomor_hp'      => $this->faker->phoneNumber(),
            'alamat'        => $this->faker->address(),
            'role'          => $this->faker->randomElement(['admin', 'sales']),
            // Jika role sales, Anda bisa meng-assign daerah_id secara manual lewat test atau menggunakan factory untuk Daerah.
            'daerah_id'     => null,  
            'username'      => $this->faker->unique()->userName(),
            'password'      => Hash::make('password'),
            'email'         => $this->faker->unique()->safeEmail(),
        ];
    }
}
