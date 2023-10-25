<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kantin>
 */
class KantinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kantin' => $this->faker->sentence,
            'foto_kantin' => 'default.jpg', 
            'deskripsi' => $this->faker->paragraph,
            'penjual_id' => User::factory(),
        ];
    }
}
