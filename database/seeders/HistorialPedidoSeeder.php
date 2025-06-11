<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistorialPedido;
use App\Models\Pedido;
use App\Models\User;

class HistorialPedidoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurarse de tener usuarios y pedidos
        User::factory(3)->create();
        Pedido::factory(10)->create();

        // Por cada pedido, crear historial de 2 a 5 transiciones
        foreach (Pedido::all() as $pedido) {
            $estados = ['pendiente', 'en_proceso', 'servido', 'pagado', 'cerrado'];
            $transiciones = [];

            for ($i = 1; $i < count($estados); $i++) {
                $transiciones[] = [
                    'pedido_id' => $pedido->id,
                    'estado_anterior' => $estados[$i - 1],
                    'estado_nuevo' => $estados[$i],
                    'usuario_id' => User::inRandomOrder()->first()->id,
                    'created_at' => now()->subMinutes(rand(10, 100)),
                    'updated_at' => now()->subMinutes(rand(10, 100)),
                ];
            }

            HistorialPedido::insert($transiciones);
        }
    }
}
