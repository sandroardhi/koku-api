<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kantin;
use App\Models\Produk;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // User::factory()->state([
        //     'tipe_user' => "penjual"
        // ])->has(Kantin::factory()->has(Produk::factory()->count(7)))->count(2)->create();
        $penjual = User::factory()->state([
            'tipe_user' => 'penjual'
        ])->count(2)->create();

        $penjual->each(function ($user) {
            $kantin = Kantin::factory()->create(['penjual_id' => $user->id]);
            $produk = Produk::factory()->count(7)->create(['penjual_id' => $user->id, 'kantin_id' => $kantin->id]);
        });

        User::factory(10)->create();
    }
}
