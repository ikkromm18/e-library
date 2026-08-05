<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@perpus.test'],
            ['name' => 'Admin', 'role' => 'admin', 'is_active' => true, 'password' => 'admin123']
        );

        for ($i = 1; $i <= 2; $i++) {
            User::firstOrCreate(
                ['email' => "petugas$i@perpus.test"],
                ['name' => "Petugas $i", 'role' => 'petugas', 'is_active' => true, 'password' => 'password']
            );
        }
    }
}
