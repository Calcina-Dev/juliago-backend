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
        $pedidos = Pedido::all();
        $usuarios = User::all();

        $estados = ['pendiente', 'en_proceso', 'servido', 'pagado', 'cerrado'];

        foreach ($pedidos as $pedido) {
            $empresaId = $pedido->empresa_id;

            $usuariosEmpresa = $usuarios->where('empresa_id', $empresaId);

            // Si no hay usuarios en la empresa, salta este pedido
            if ($usuariosEmpresa->isEmpty()) {
                continue;
            }

            $transiciones = [];

            for ($i = 1; $i < count($estados); $i++) {
                $transiciones[] = [
                    'pedido_id' => $pedido->id,
                    'estado_anterior' => $estados[$i - 1],
                    'estado_nuevo' => $estados[$i],
                    'usuario_id' => $usuariosEmpresa->random()->id,
                    'empresa_id' => $empresaId, // ✅ clave nueva
                    'created_at' => now()->subMinutes(rand(10, 100)),
                    'updated_at' => now()->subMinutes(rand(10, 100)),
                ];
            }

            HistorialPedido::insert($transiciones);
        }
    }
}
