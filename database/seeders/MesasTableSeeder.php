<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mesa;
use App\Models\Empresa;

class MesasTableSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener la empresa DoñaJulia como ejemplo base
        $empresa = Empresa::where('nombre', 'DoñaJulia')->first();

        // Crear algunas mesas de ejemplo
        $nombres = ['Mesa 1', 'Mesa 2', 'Mesa 3', 'Mesa VIP'];

        foreach ($nombres as $nombre) {
            Mesa::create([
                'nombre' => $nombre,
                'estado' => 'libre',
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
