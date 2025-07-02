<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,          // 🔹 Primero empresas
            UserSeeder::class,             // 🔹 Luego usuarios con empresa_id
            CategoriasTableSeeder::class,  // 🔹 Luego dependientes
            MesasTableSeeder::class,
            ProductosTableSeeder::class,
             MenuSeeder::class,
             MenuProductoSeeder::class,
            PedidosTableSeeder::class,
            HistorialPedidoSeeder::class,
           
        ]);
    }
}
