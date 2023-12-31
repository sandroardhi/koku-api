<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kantin;
use App\Models\Produk;
use App\Models\Kategori;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'level' => 'admin'
        ]);

        $users = User::factory(10)->create();

        $penjuals = User::factory()->count(4)->create([
            'level' => 'penjual'
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $penjualRole = Role::where('name', 'penjual')->first();

        $admin->assignRole($adminRole->id);
        foreach ($users as $user) {
            $user->assignRole($userRole->id);
        }
        
        foreach ($penjuals as $penjual) {
            $penjual->assignRole($penjualRole->id);
        }

        $namaKategori = ['Nasi', 'Snack', 'Mie', 'Minuman'];

        $kategori = collect($namaKategori)->map(function ($name) {
            return Kategori::factory()->create(['nama' => $name]);
        });


        $penjuals->each(function ($user) use ($kategori) {
            $kantin = Kantin::factory()->create(['penjual_id' => $user->id]);
            $produk = Produk::factory()->count(7)->create(['penjual_id' => $user->id, 'kantin_id' => $kantin->id, 'kategori_id' => $kategori->pluck('id')->random()]);
        });

    }
}
