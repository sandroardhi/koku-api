<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kantin;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('role', 'User')->first()->id,
        ]);
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('role', 'Admin')->first()->id,

        ]);

        $penjual = User::factory()->state([
            'role_id' => Role::where('role', 'Penjual')->first()->id,
        ])->count(4)->create();

        $namaKategori = ['Nasi', 'Snack', 'Mie', 'Minuman'];

        $kategori = collect($namaKategori)->map(function ($name) {
            return Kategori::factory()->create(['nama' => $name]);
        });


        $penjual->each(function ($user) use ($kategori) {
            $kantin = Kantin::factory()->create(['penjual_id' => $user->id]);
            $produk = Produk::factory()->count(7)->create(['penjual_id' => $user->id, 'kantin_id' => $kantin->id, 'kategori_id' => $kategori->pluck('id')->random()]);
        });

        User::factory(10)->create();
    }
}
