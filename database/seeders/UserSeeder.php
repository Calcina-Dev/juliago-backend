<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Superadmin (sin empresa asociada)
        User::updateOrCreate(
            ['email' => 'superadmin@juliago.local'],
            [
                'name'        => 'Superadmin',
                'password'    => Hash::make('superadmin123'),
                'rol'         => 'superadmin',
                'empresa_id'  => null,
            ]
        );

        // Obtener empresa base
        $empresa = Empresa::where('nombre', 'DoñaJulia')->first();

        if (!$empresa) {
            $this->command->error('❌ No se encontró la empresa DoñaJulia. Seeder cancelado.');
            return;
        }

        // Usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@juliago.local'],
            [
                'name'        => 'Administrador',
                'password'    => Hash::make('admin123'),
                'rol'         => 'admin',
                'empresa_id'  => $empresa->id,
            ]
        );

        // Usuario Mesero
        User::updateOrCreate(
            ['email' => 'mesero@juliago.local'],
            [
                'name'        => 'Mesero',
                'password'    => Hash::make('mesero123'),
                'rol'         => 'mesero',
                'empresa_id'  => $empresa->id,
            ]
        );

        // Usuario Cajero
        User::updateOrCreate(
            ['email' => 'cajero@juliago.local'],
            [
                'name'        => 'Cajero',
                'password'    => Hash::make('cajero123'),
                'rol'         => 'cajero',
                'empresa_id'  => $empresa->id,
            ]
        );

        // Usuario Cocinero
        User::updateOrCreate(
            ['email' => 'cocinero@juliago.local'],
            [
                'name'        => 'Cocinero',
                'password'    => Hash::make('cocinero123'),
                'rol'         => 'cocinero',
                'empresa_id'  => $empresa->id,
            ]
        );

        // Usuarios Cliente ejemplo
        User::updateOrCreate(
            ['email' => 'cliente1@juliago.local'],
            [
                'name'        => 'Cliente Uno',
                'password'    => Hash::make('cliente123'),
                'rol'         => 'cliente',
                'empresa_id'  => $empresa->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente2@juliago.local'],
            [
                'name'        => 'Cliente Dos',
                'password'    => Hash::make('cliente123'),
                'rol'         => 'cliente',
                'empresa_id'  => $empresa->id,
            ]
        );

        $this->command->info('✅ Usuarios creados para DoñaJulia y Superadmin');
    }
}
