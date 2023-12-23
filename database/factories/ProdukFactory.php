<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Kantin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'foto' => 'default.jpg', 
            'nama' => $this->faker->word,
            'deskripsi' => $this->faker->text,
            'harga' => $this->faker->numberBetween(7000, 20000),
            'kuantitas' => $this->faker->numberBetween(1, 20),
            'penjual_id' => User::factory(),
            'kantin_id' => Kantin::factory(),
        ];
    }
}
