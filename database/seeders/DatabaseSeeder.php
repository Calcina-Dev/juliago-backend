<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    $this->call([
        CategoriasTableSeeder::class,
        MesasTableSeeder::class,
        ProductosTableSeeder::class,
        UserSeeder::class,
        PedidosTableSeeder::class,
        HistorialPedidoSeeder::class,
    ]);

    }

}
