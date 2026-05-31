<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['email' => 'admin@albaik.test', 'name' => 'Demo Admin', 'mobile' => '+963900000001', 'type' => 'admin', 'role' => 'Admin'],
            ['email' => 'manager@albaik.test', 'name' => 'Electronics Manager', 'mobile' => '+963900000004', 'type' => 'manager', 'role' => 'Manager'],
            ['email' => 'customer@albaik.test', 'name' => 'Demo Customer', 'mobile' => '+963900000002', 'type' => 'customer', 'role' => 'Customer'],
            ['email' => 'wholesale@albaik.test', 'name' => 'Wholesale Electronics Customer', 'mobile' => '+963900000003', 'type' => 'wholesale_customer', 'role' => 'Wholesale Customer'],
        ];

        foreach ($users as $payload) {
            $user = User::updateOrCreate(['email' => $payload['email']], [
                'name' => $payload['name'],
                'mobile' => $payload['mobile'],
                'type' => $payload['type'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            if (method_exists($user, 'syncRoles')) $user->syncRoles([$payload['role']]);
        }
    }
}
