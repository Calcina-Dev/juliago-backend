<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductosTableSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::all();

        if ($categorias->count() < 3) {
            $this->command->warn('⚠️ Se necesitan al menos 3 categorías para una buena distribución.');
            return;
        }

        // Creamos 15 productos repartidos en todas las categorías
        foreach ($categorias as $categoria) {
            foreach (range(1, 5) as $i) {
                Producto::create([
                    'nombre'       => $categoria->nombre . ' Producto ' . $i,
                    'precio'       => rand(8, 40),
                    'descripcion'  => 'Producto demo de la categoría ' . $categoria->nombre,
                    'categoria_id' => $categoria->id,
                ]);
            }
        }

        $this->command->info('✅ Se crearon productos distribuidos en todas las categorías.');
    }
}
