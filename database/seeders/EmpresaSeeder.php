<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'nombre' => 'DoñaJulia',
            'logo' => null,
            'moneda' => 'PEN',
            'modo_mantenimiento' => false,
        ]);

        Empresa::create([
            'nombre' => 'Cocodrink',
            'logo' => null,
            'moneda' => 'PEN',
            'modo_mantenimiento' => false,
        ]);
    }
}
