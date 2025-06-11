<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@juliago.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'rol' => 'admin',
            ]
        );

        // Usuario Mesero
        User::updateOrCreate(
            ['email' => 'mesero@juliago.local'],
            [
                'name' => 'Mesero',
                'password' => Hash::make('mesero123'),
                'rol' => 'mesero',
            ]
        );

        // Usuario Cajero
        User::updateOrCreate(
            ['email' => 'cajero@juliago.local'],
            [
                'name' => 'Cajero',
                'password' => Hash::make('cajero123'),
                'rol' => 'cajero',
            ]
        );

        // Usuario Cocinero
        User::updateOrCreate(
            ['email' => 'cocinero@juliago.local'],
            [
                'name' => 'Cocinero',
                'password' => Hash::make('cocinero123'),
                'rol' => 'cocinero',
            ]
        );
    }
}
