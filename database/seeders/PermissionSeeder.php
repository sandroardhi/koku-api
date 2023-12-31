<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-role',
            'read-role',
            'update-role',
            'delete-role',
            'create-user',
            'read-user',
            'read-user-list',
            'update-user',
            'delete-user',
            'create-kantin',
            'read-kantin',
            'read-kantin-list',
            'update-kantin',
            'delete-kantin',
            'create-kategori',
            'read-kategori',
            'read-kategori-list',
            'update-kategori',
            'delete-kategori',
            'create-produk',
            'read-produk',
            'read-produk-list',
            'update-produk',
            'delete-produk',
        ];

        foreach($permissions as $permission) {
            Permission::create(['name' => $permission]);
        };
    }
}
