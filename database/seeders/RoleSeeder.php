<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $adminPermissions = Permission::pluck('id', 'id')->all();
        $adminRole->syncPermissions($adminPermissions);

        $penjualRole = Role::create(['name' => 'penjual']);
        $penjualPermissions = ['read-user', 'update-user', 'delete-user', 'read-kantin', 'create-kantin', 'update-kantin', 'delete-kantin', 'create-produk', 'read-produk', 'read-produk-list', 'update-produk', 'delete-produk','read-order'];
        $penjualPermissionsIds = Permission::whereIn('name', $penjualPermissions)->pluck('id')->toArray();
        $penjualRole->syncPermissions($penjualPermissionsIds);

        $pengantarRole = Role::create(['name' => 'pengantar']);
        $pengantarPermissions = ['read-user', 'update-user', 'delete-user', 'read-kantin', 'read-kategori', 'read-order'];;
        $pengantarPermissionsIds = Permission::whereIn('name', $pengantarPermissions)->pluck('id')->toArray();
        $pengantarRole->syncPermissions($pengantarPermissionsIds);

        $userRole = Role::create(['name' => 'user']);
        $userPermissions = ['read-user', 'update-user', 'delete-user', 'read-kantin', 'read-kategori', 'read-produk', 'create-keranjang', 'read-keranjang', 'update-keranjang', 'delete-keranjang'];;
        $userPermissionsIds = Permission::whereIn('name', $userPermissions)->pluck('id')->toArray();
        $userRole->syncPermissions($userPermissionsIds);
    }
}
