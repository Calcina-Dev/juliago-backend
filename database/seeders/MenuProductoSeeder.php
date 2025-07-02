<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Producto;

class MenuProductoSeeder extends Seeder
{
    public function run(): void
    {
        $menus = Menu::all();
        $productos = Producto::all();

        if ($menus->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('⚠️ No hay menús o productos disponibles para asignar.');
            return;
        }

        foreach ($menus as $menu) {
            $productosRandom = $productos->random(rand(5, 10))->unique('id');

            foreach ($productosRandom as $producto) {
                if (!$menu->productos()->where('producto_id', $producto->id)->exists()) {
                    $menu->productos()->attach($producto->id, [
                        'precio' => rand(8, 30),
                    ]);
                }
            }

            $this->command->info("✅ Productos asignados al menú {$menu->nombre}");
        }
    }
}
