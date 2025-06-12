<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Pago;
use App\Models\Mesa;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PedidosTableSeeder extends Seeder
{
    public function run(): void
    {
        $mesas     = Mesa::all();
        $menu      = Menu::where('es_actual', true)->with('productos')->first();
        $cajero    = User::where('rol', 'cajero')->inRandomOrder()->first();
        $clientes  = User::where('rol', 'cliente')->get();

        if (!$menu || $menu->productos->isEmpty()) {
            $this->command->warn('⚠️ No hay menú activo o productos asignados al menú.');
            return;
        }

        if ($clientes->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios con rol cliente para asignar a los pedidos.');
            return;
        }

        // Pedidos para semana actual y pasada
        $this->generarPedidos($mesas, $menu, $cajero, $clientes, now()->startOfWeek(), 'semana actual');
        $this->generarPedidos($mesas, $menu, $cajero, $clientes, now()->subWeek()->startOfWeek(), 'semana pasada');
    }

    private function generarPedidos($mesas, $menu, $cajero, $clientes, Carbon $inicioSemana, string $etiqueta)
    {
        foreach (range(0, 6) as $i) {
            foreach (range(1, rand(3, 6)) as $j) {
                $fechaBase = $inicioSemana->copy()->addDays($i);

                $franja = collect([
                    ['inicio' => 6, 'fin' => 11],   // Mañana
                    ['inicio' => 12, 'fin' => 17],  // Tarde
                    ['inicio' => 18, 'fin' => 22],  // Noche
                ])->random();

                $fecha = $fechaBase->copy()
                    ->setHour(rand($franja['inicio'], $franja['fin']))
                    ->setMinute(rand(0, 59));

                $mesa = $mesas->random();
                $cliente = $clientes->random();
                $estado = collect(['pagado', 'cerrado', 'servido'])->random();

                $pedido = Pedido::create([
                    'mesa_id'    => $mesa->id,
                    'usuario_id' => $cliente->id,
                    'estado'     => $estado,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);

                $this->command->info("Pedido #{$pedido->id} creado para cliente {$cliente->name} (ID: {$cliente->id}), mesa {$mesa->id}, estado '{$estado}'");

                $total = 0;

                foreach ($menu->productos->random(rand(1, 3)) as $producto) {
                    $cantidad = rand(1, 3);
                    $precio   = $producto->pivot->precio; // Precio del menú actual
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

                $pedido->update(['total' => $total]);

                if ($estado === 'pagado') {
                    Pago::create([
                        'pedido_id'   => $pedido->id,
                        'usuario_id'  => $cajero?->id,
                        'monto'       => $total,
                        'metodo_pago' => 'efectivo',
                        'pagado_en'   => $fecha,
                        'created_at'  => $fecha,
                        'updated_at'  => $fecha,
                    ]);
                }
            }
        }

        $this->command->info("✅ Datos insertados para {$etiqueta}");
    }
}
