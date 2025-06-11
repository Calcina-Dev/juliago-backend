<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Pago;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Carbon;

class PedidosTableSeeder extends Seeder
{
    public function run(): void
    {
        $mesas     = Mesa::all();
        $productos = Producto::all();
        $cajero    = User::where('rol', 'cajero')->inRandomOrder()->first();

        // Generar pedidos para la semana actual y la semana pasada
        $this->generarPedidos($mesas, $productos, $cajero, now()->startOfWeek(), 'semana actual');
        $this->generarPedidos($mesas, $productos, $cajero, now()->subWeek()->startOfWeek(), 'semana pasada');
    }

   private function generarPedidos($mesas, $productos, $cajero, Carbon $inicioSemana, string $etiqueta)
{
    foreach (range(0, 6) as $i) {
        foreach (range(1, rand(3, 6)) as $j) {
            $fechaBase = $inicioSemana->copy()->addDays($i);

            // Elegir franja horaria aleatoria
            $franja = collect([
                ['inicio' => 6, 'fin' => 11],   // Mañana
                ['inicio' => 12, 'fin' => 17],  // Tarde
                ['inicio' => 18, 'fin' => 22],  // Noche
            ])->random();

            $fecha = $fechaBase->copy()
                ->setHour(rand($franja['inicio'], $franja['fin']))
                ->setMinute(rand(0, 59));

            $mesa = $mesas->random();
            $estado = collect(['pagado', 'cerrado', 'servido'])->random();

            $pedido = Pedido::create([
                'mesa_id'    => $mesa->id,
                'estado'     => $estado,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            $total = 0;

            foreach (range(1, rand(1, 3)) as $_) {
                $producto = $productos->random();
                $cantidad = rand(1, 3);
                $precio   = $producto->precio;
                $subtotal = $cantidad * $precio;

                PedidoDetalle::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $subtotal,
                    'created_at'      => $fecha,
                    'updated_at'      => $fecha,
                ]);

                $total += $subtotal;
            }

            if ($estado === 'pagado') {
                Pago::create([
                    'pedido_id'   => $pedido->id,
                    'usuario_id'  => $cajero->id,
                    'monto'       => $total,
                    'metodo_pago' => 'efectivo',
                    'pagado_en'   => $fecha,
                    'created_at'  => $fecha,
                    'updated_at'  => $fecha,
                ]);
            }
        }
    }

    echo "✅ Datos insertados para {$etiqueta}\n";
}

}
