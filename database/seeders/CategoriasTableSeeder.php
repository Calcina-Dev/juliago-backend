<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Empresa;

class CategoriasTableSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar empresa base
        $empresa = Empresa::where('nombre', 'DoñaJulia')->first();

        $categorias = ['Bebidas', 'Entradas', 'Platos de fondo', 'Postres'];

        foreach ($categorias as $nombre) {
            Categoria::create([
                'nombre' => $nombre,
                'destino' => $nombre === 'Bebidas' ? 'bar' : 'cocina',
                'empresa_id' => $empresa->id,
            ]);
        }
    }
}
