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
        // create user
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
        $pengantar1 = User::factory()->create([
            'name' => 'pengantar1',
            'email' => 'pengantar1@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $pengantar2 = User::factory()->create([
            'name' => 'pengantar2',
            'email' => 'pengantar2@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $users = User::factory(10)->create();
        $penjuals = User::factory()->count(4)->create();
        // end of create user

        // assign user's role
        $adminRole = Role::where('name', 'admin')->first();

        $userRole = Role::where('name', 'user')->first();

        $penjualRole = Role::where('name', 'penjual')->first();

        $pengantarRole = Role::where('name', 'pengantar')->first();

        $admin->assignRole($adminRole->id);

        $sandro->assignRole($userRole->id);

        $penjual1->assignRole($penjualRole->id);

        $penjual2->assignRole($penjualRole->id);

        $pengantar1->assignRole($pengantarRole->id);

        $pengantar2->assignRole($pengantarRole->id);

        foreach ($users as $user) {
            $user->assignRole($userRole->id);
        }

        foreach ($penjuals as $penjual) {
            $penjual->assignRole($penjualRole->id);
        }
        // end of assign user's role
        
        // kategori
        $namaKategori = ['Nasi', 'Snack', 'Mie', 'Minuman'];

        $kategori = collect($namaKategori)->map(function ($name) {
            return Kategori::factory()->create(['nama' => $name, 'foto' => 'foto_kategori/default.jpg']);
        });
        // end of kategori

        // assign kantin for penjual
        $kantin1 = Kantin::factory()->withNama('Kantin Penjual 1')->create(['penjual_id' => $penjual1->id]);
        $produk1 = Produk::factory()->count(7)->create([
            'penjual_id' => $penjual1->id,
            'kantin_id' => $kantin1->id,
            'kategori_id' => $kategori->pluck('id')->random(),
        ]);
        $penjual1->kantin()->save($kantin1);
        $kantin1->produks()->saveMany($produk1);

        $kantin2 = Kantin::factory()->withNama('Kantin Penjual 2')->create(['penjual_id' => $penjual2->id]);
        $produk2 = Produk::factory()->count(7)->create([
            'penjual_id' => $penjual1->id,
            'kantin_id' => $kantin2->id,
            'kategori_id' => $kategori->pluck('id')->random(),
        ]);
        $penjual2->kantin()->save($kantin2);
        $kantin2->produks()->saveMany($produk2);

        $penjuals->each(function ($user) use ($kategori) {
            $kantin = Kantin::factory()->create(['penjual_id' => $user->id]);
            $produk = Produk::factory()->count(7)->create(['penjual_id' => $user->id, 'kantin_id' => $kantin->id, 'kategori_id' => $kategori->pluck('id')->random()]);
        });
        // end of assign kantin for penjual
    }
}
