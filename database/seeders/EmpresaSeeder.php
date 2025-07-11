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
            'activa' => true, // ✅ importante
        ]);

        Empresa::create([
            'nombre' => 'Cocodrink',
            'logo' => null,
            'moneda' => 'PEN',
            'modo_mantenimiento' => false,
            'activa' => false, // ❌ empresa inactiva (puedes probar con este caso)
        ]);

    }
}
