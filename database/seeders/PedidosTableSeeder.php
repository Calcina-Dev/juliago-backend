<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Pago;
use App\Models\Mesa;
use App\Models\User;
use App\Models\Menu;
use App\Models\Empresa;
use Illuminate\Support\Carbon;

class PedidosTableSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $this->command->info("▶ Generando pedidos para empresa: {$empresa->nombre}");

            $mesas     = Mesa::where('empresa_id', $empresa->id)->get();
            $menu      = Menu::where('empresa_id', $empresa->id)->where('es_actual', true)->with('productos')->first();
            $cajero    = User::where('empresa_id', $empresa->id)->where('rol', 'cajero')->inRandomOrder()->first();
            $clientes  = User::where('empresa_id', $empresa->id)->where('rol', 'cliente')->get();

            if (!$menu || $menu->productos->isEmpty()) {
                $this->command->warn("⚠️ Menú vacío o sin productos para {$empresa->nombre}");
                continue;
            }

            if ($clientes->isEmpty()) {
                $this->command->warn("⚠️ No hay clientes en {$empresa->nombre}");
                continue;
            }

            $this->generarPedidos($empresa->id, $mesas, $menu, $cajero, $clientes, now()->startOfWeek(), 'semana actual');
            $this->generarPedidos($empresa->id, $mesas, $menu, $cajero, $clientes, now()->subWeek()->startOfWeek(), 'semana pasada');
        }
    }

    private function generarPedidos($empresaId, $mesas, $menu, $cajero, $clientes, Carbon $inicioSemana, string $etiqueta)
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

                $mesa    = $mesas->random();
                $cliente = $clientes->random();
                $estado  = collect(['pagado', 'cerrado', 'servido'])->random();

                $pedido = Pedido::create([
                    'mesa_id'     => $mesa->id,
                    'usuario_id'  => $cliente->id,
                    'empresa_id'  => $empresaId,
                    'estado'      => $estado,
                    'created_at'  => $fecha,
                    'updated_at'  => $fecha,
                ]);

                $total = 0;

                foreach ($menu->productos->random(rand(1, 3)) as $producto) {
                    $cantidad = rand(1, 3);
                    $precio   = $producto->pivot->precio;
                    $subtotal = $cantidad * $precio;

                    PedidoDetalle::create([
                        'pedido_id'       => $pedido->id,
                        'producto_id'     => $producto->id,
                        'empresa_id'      => $empresaId,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal'        => $subtotal,
                        'created_at'      => $fecha,
                        'updated_at'      => $fecha,
                    ]);

                    $total += $subtotal;
                }

                $pedido->update(['total' => $total]);

                if ($estado === 'pagado' && $cajero) {
                    Pago::create([
                        'pedido_id'   => $pedido->id,
                        'usuario_id'  => $cajero->id,
                        'empresa_id'  => $empresaId,
                        'monto'       => $total,
                        'metodo_pago' => 'efectivo',
                        'pagado_en'   => $fecha,
                        'created_at'  => $fecha,
                        'updated_at'  => $fecha,
                    ]);
                }

                $this->command->info("✔ Pedido #{$pedido->id} para cliente {$cliente->name} (Empresa {$empresaId}) en estado '{$estado}'");
            }
        }

        $this->command->info("✅ Datos generados para {$etiqueta} (empresa ID: {$empresaId})");
    }
}
