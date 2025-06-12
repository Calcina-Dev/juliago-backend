<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Producto;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Crear menú activo
        $menu = Menu::create([
            'nombre' => 'Menú regular',
            'es_actual' => true,
        ]);

        // Obtener productos existentes
        $productos = Producto::all();

        // Asociar productos con precios específicos al menú
        foreach ($productos as $producto) {
            $precioMenu = $producto->precio * 0.95; // ejemplo: 5% de descuento en el menú

            $menu->productos()->attach($producto->id, [
                'precio' => round($precioMenu, 2),
            ]);
        }
    }
}
