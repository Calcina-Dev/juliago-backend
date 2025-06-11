<?php

namespace App\Http\Controllers\Api;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\HistorialPedido;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{

    

    public function index(Request $request)
    {
        $query = Pedido::with('detalles.producto', 'mesa');

        // Si envían un filtro por estado (opcional)
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json($query->get());
    }

  public function cambiarEstado(Request $request, $id)
{
    $pedido = Pedido::findOrFail($id);

    // Estados que no se deben modificar nunca más
    if (in_array($pedido->estado, ['cerrado', 'cancelado'])) {
        return response()->json(['error' => 'El pedido está ' . $pedido->estado . ' y no puede cambiar de estado.'], 403);
    }

    // Validación del nuevo estado
    $data = $request->validate([
        'estado' => 'required|in:pendiente,en_proceso,servido,pagado,cancelado,cerrado',
    ]);

    $estadoActual = $pedido->estado;
    $nuevoEstado = $data['estado'];

    // Reglas de transiciones válidas
    $transicionesValidas = [
        'pendiente'   => ['en_proceso', 'cancelado'],
        'en_proceso'  => ['servido', 'cancelado'],
        'servido'     => ['pagado'],
        'pagado'      => ['cerrado'],
    ];

    if (
        !isset($transicionesValidas[$estadoActual]) ||
        !in_array($nuevoEstado, $transicionesValidas[$estadoActual])
    ) {
        return response()->json([
            'error' => "Transición no permitida de '$estadoActual' a '$nuevoEstado'"
        ], 422);
    }

    // ✅ Registrar el historial ANTES de guardar
    HistorialPedido::create([
        'pedido_id' => $pedido->id,
        'estado_anterior' => $estadoActual,
        'estado_nuevo' => $nuevoEstado,
        'usuario_id' => auth()->id(),
    ]);

    // Guardar el nuevo estado
    $pedido->estado = $nuevoEstado;
    $pedido->save();

    return response()->json(['mensaje' => 'Estado actualizado', 'pedido' => $pedido]);
}




    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        // Verificar si la mesa ya tiene un pedido activo
        $pedidoExistente = Pedido::where('mesa_id', $request->mesa_id)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->first();

        if ($pedidoExistente) {
            return response()->json(['mensaje' => 'La mesa ya tiene un pedido activo.'], 400);
        }

        DB::beginTransaction();

        try {
            $pedido = Pedido::create([
                'mesa_id' => $request->mesa_id,
                'estado' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $cantidad = $item['cantidad'];
                $precio = $producto->precio;
                $subtotal = $precio * $cantidad;

                PedidoDetalle::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            DB::commit();

            return response()->json($pedido->load('detalles'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el pedido', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Opcional: evitar cancelar si ya está pagado o cerrado
        if (in_array($pedido->estado, ['pagado', 'cerrado'])) {
            return response()->json(['mensaje' => 'No se puede cancelar un pedido pagado o cerrado'], 400);
        }

        $pedido->delete();

        return response()->json(['mensaje' => 'Pedido cancelado (soft delete)']);
    }

 public function porMesa(Request $request, $id)
    {
        $esHistorial = $request->query('historial') === 'true';

        $query = Pedido::withTrashed()
            ->with('detalles.producto')
            ->where('mesa_id', $id)
            ->orderByDesc('created_at');

        if (!$esHistorial) {
            $query->whereIn('estado', ['pendiente', 'en_proceso', 'servido']);
        }

        $pedidos = $query->get();

        if (!$esHistorial && $pedidos->isEmpty()) {
            return response()->json([
                'mensaje' => 'No hay pedido activo para esta mesa.'
            ], 404);
        }

        return response()->json($pedidos);
    }



    public function activos()
    {
        $pedidos = Pedido::with('detalles.producto')
            ->whereIn('estado', ['pendiente', 'en_proceso', 'servido'])
            ->get();

        return response()->json($pedidos);
    }

    public function show($id)
    {
        $pedido = Pedido::with(['mesa', 'detalles.producto', 'pagos'])->findOrFail($id);
        return response()->json($pedido);
    }

    public function agregarProducto(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!in_array($pedido->estado, ['pendiente', 'en_proceso'])) {
            return response()->json(['error' => 'No se puede modificar un pedido en estado ' . $pedido->estado], 403);
        }

        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = \App\Models\Producto::findOrFail($data['producto_id']);
        $subtotal = $data['cantidad'] * $producto->precio;

        $detalle = \App\Models\PedidoDetalle::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $producto->precio,
            'subtotal' => $subtotal,
        ]);

        return response()->json(['mensaje' => 'Producto agregado', 'detalle' => $detalle], 201);
    }


    public function quitarProducto($pedidoId, $productoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        if (!in_array($pedido->estado, ['pendiente', 'en_proceso'])) {
            return response()->json(['error' => 'No se puede modificar un pedido en estado ' . $pedido->estado], 403);
        }

        $detalle = \App\Models\PedidoDetalle::where('pedido_id', $pedidoId)
                    ->where('producto_id', $productoId)
                    ->firstOrFail();

        $detalle->delete();

        return response()->json(['mensaje' => 'Producto eliminado del pedido']);
    }

    public function estadosValidos($id)
    {
        $pedido = Pedido::findOrFail($id);

        $transicionesValidas = [
            'pendiente'   => ['en_proceso', 'cancelado'],
            'en_proceso'  => ['servido', 'cancelado'],
            'servido'     => ['pagado'],
            'pagado'      => ['cerrado'],
        ];

        $labels = [
            'pendiente'   => 'Pendiente',
            'en_proceso'  => 'En proceso',
            'servido'     => 'Servido',
            'pagado'      => 'Pagado',
            'cerrado'     => 'Cerrado',
            'cancelado'   => 'Cancelado',
        ];

        if (in_array($pedido->estado, ['cerrado', 'cancelado'])) {
            return response()->json(['estados' => []]);
        }

        $estados = collect($transicionesValidas[$pedido->estado] ?? [])
            ->map(fn($estado) => [
                'value' => $estado,
                'label' => $labels[$estado] ?? ucfirst($estado),
            ])
            ->values();

        return response()->json(['estados' => $estados]);
    }

    public function todosLosEstados()
    {
        $estados = [
            'pendiente'   => 'Pendiente',
            'en_proceso'  => 'En proceso',
            'servido'     => 'Servido',
            'pagado'      => 'Pagado',
            'cerrado'     => 'Cerrado',
            'cancelado'   => 'Cancelado',
        ];

        $respuesta = collect($estados)->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values();

        return response()->json(['estados' => $respuesta]);
    }





}
