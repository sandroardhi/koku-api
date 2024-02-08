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
        ]);
        $sandro = User::factory()->create([
            'name' => 'Sandro',
            'email' => 'sandro@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $penjual1 = User::factory()->create([
            'name' => 'penjual 1',
            'email' => 'penjual1@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $penjual2 = User::factory()->create([
            'name' => 'penjual2',
            'email' => 'penjual2@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $namaKategori = ['Nasi', 'Snack', 'Mie', 'Minuman'];

        $kategori = collect($namaKategori)->map(function ($name) {
            return Kategori::factory()->create(['nama' => $name]);
        });

        $users = User::factory(10)->create();

        $penjuals = User::factory()->count(4)->create();

        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $penjualRole = Role::where('name', 'penjual')->first();

        $admin->assignRole($adminRole->id);

        $sandro->assignRole($userRole->id);

        $penjual1->assignRole($penjualRole->id);

        // Create Kantin and Produk for penjual1
        $kantin1 = Kantin::factory()->withNama('Kantin Penjual 1')->create(['penjual_id' => $penjual1->id]);
        $produk1 = Produk::factory()->count(7)->create([
            'penjual_id' => $penjual1->id,
            'kantin_id' => $kantin1->id,
            'kategori_id' => $kategori->pluck('id')->random(),
        ]);
        $penjual1->kantin()->save($kantin1);
        $kantin1->produks()->saveMany($produk1);

        $penjual2->assignRole($penjualRole->id);
        $kantin2 = Kantin::factory()->withNama('Kantin Penjual 2')->create(['penjual_id' => $penjual2->id]);
        $produk2 = Produk::factory()->count(7)->create([
            'penjual_id' => $penjual1->id,
            'kantin_id' => $kantin2->id,
            'kategori_id' => $kategori->pluck('id')->random(),
        ]);
        $penjual2->kantin()->save($kantin2);
        $kantin2->produks()->saveMany($produk2);



        foreach ($users as $user) {
            $user->assignRole($userRole->id);
        }

        foreach ($penjuals as $penjual) {
            $penjual->assignRole($penjualRole->id);
        }


        $penjuals->each(function ($user) use ($kategori) {
            $kantin = Kantin::factory()->create(['penjual_id' => $user->id]);
            $produk = Produk::factory()->count(7)->create(['penjual_id' => $user->id, 'kantin_id' => $kantin->id, 'kategori_id' => $kategori->pluck('id')->random()]);
        });
    }
}
