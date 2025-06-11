<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasTableSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = ['Bebidas', 'Entradas', 'Platos de fondo', 'Postres'];

        foreach ($categorias as $nombre) {
            Categoria::create(['nombre' => $nombre]);
        }
    }
}
