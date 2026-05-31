<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Super Admin', 'Admin', 'Manager', 'Customer', 'Wholesale Customer'];
        foreach ($roles as $role) Role::firstOrCreate(['name' => $role]);

        $permissions = ['manage products','manage orders','manage users','manage settings','manage inventory','manage marketing','manage shipping','manage offers'];
        foreach ($permissions as $permission) Permission::firstOrCreate(['name' => $permission]);

        Role::findByName('Super Admin')->syncPermissions($permissions);
        Role::findByName('Admin')->syncPermissions($permissions);
        Role::findByName('Manager')->syncPermissions(['manage products','manage orders','manage inventory','manage shipping','manage offers']);
    }
}
