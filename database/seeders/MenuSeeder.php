<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Producto;
use App\Models\Empresa;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            // Crear menú para esta empresa
            $menu = Menu::create([
                'nombre' => 'Menú regular',
                'es_actual' => true,
                'empresa_id' => $empresa->id,
            ]);

            // Obtener productos de esta empresa
            $productos = Producto::where('empresa_id', $empresa->id)->get();

            foreach ($productos as $producto) {
                $precioMenu = $producto->precio * 0.95; // 5% descuento

                $menu->productos()->attach($producto->id, [
                    'precio' => round($precioMenu, 2),
                ]);
            }
        }
    }
}
