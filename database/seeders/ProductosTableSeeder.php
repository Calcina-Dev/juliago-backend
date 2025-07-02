<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductosTableSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::with('empresa')->get();

        if ($categorias->count() < 3) {
            $this->command->warn('⚠️ Se necesitan al menos 3 categorías para una buena distribución.');
            return;
        }

        foreach ($categorias as $categoria) {
            // Asegurarse que la categoría tenga empresa_id
            if (!$categoria->empresa_id) {
                $this->command->warn("⚠️ Categoría '{$categoria->nombre}' no tiene empresa_id, se omite.");
                continue;
            }

            foreach (range(1, 5) as $i) {
                Producto::create([
                    'nombre'       => $categoria->nombre . ' Producto ' . $i,
                    'precio'       => rand(8, 40),
                    'descripcion'  => 'Producto demo de la categoría ' . $categoria->nombre,
                    'categoria_id' => $categoria->id,
                    'empresa_id'   => $categoria->empresa_id, // ✅ agregado
                ]);
            }
        }

        $this->command->info('✅ Se crearon productos distribuidos en todas las categorías.');
    }
}
