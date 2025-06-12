<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\Menu;
use App\Models\HistorialPedido;

class PedidoController extends Controller
{

public function storeCliente(Request $request)
{
    $user = $request->user();
    // Validaciones y creación de pedido
    $pedido = Pedido::create([
        'mesa_id' => $request->mesa_id,
        'usuario_id' => $user->id,
        'estado' => 'pendiente',
        'total' => 0,
    ]);
    // Resto lógica...
}

public function indexCliente(Request $request)
{
    $user = $request->user();
    $pedidos = Pedido::with('detalles.producto')
        ->where('usuario_id', $user->id)
        ->get();
    return response()->json($pedidos);
}

public function showCliente($id)
{
    $user = auth()->user();
    $pedido = Pedido::with('detalles.producto')
        ->where('id', $id)
        ->where('usuario_id', $user->id)
        ->firstOrFail();
    return response()->json($pedido);
}



    public function index(Request $request)
    {
        $query = Pedido::with('detalles.producto', 'mesa');

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $pedidoExistente = Pedido::where('mesa_id', $request->mesa_id)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->first();

        if ($pedidoExistente) {
            return response()->json(['mensaje' => 'La mesa ya tiene un pedido activo.'], 400);
        }

        DB::beginTransaction();

        try {
            $menu = Menu::where('es_actual', true)->with('productos')->firstOrFail();

            $pedido = Pedido::create([
                'mesa_id' => $request->mesa_id,
                'estado' => 'pendiente',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($request->productos as $item) {
                $producto = $menu->productos->firstWhere('id', $item['producto_id']);
                if (!$producto) {
                    throw new \Exception("El producto ID {$item['producto_id']} no está en el menú actual.");
                }

                $cantidad = $item['cantidad'];
                $precio = $producto->pivot->precio;
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
            return response()->json([
                'error' => 'Error al crear el pedido',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (in_array($pedido->estado, ['cerrado', 'cancelado'])) {
            return response()->json(['error' => 'El pedido está ' . $pedido->estado . ' y no puede cambiar de estado.'], 403);
        }

        $data = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,servido,pagado,cancelado,cerrado',
        ]);

        $estadoActual = $pedido->estado;
        $nuevoEstado = $data['estado'];

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

        HistorialPedido::create([
            'pedido_id' => $pedido->id,
            'estado_anterior' => $estadoActual,
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => auth()->id(),
        ]);

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        return response()->json(['mensaje' => 'Estado actualizado', 'pedido' => $pedido]);
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

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
            return response()->json(['mensaje' => 'No hay pedido activo para esta mesa.'], 404);
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

        $menu = Menu::where('es_actual', true)->with('productos')->firstOrFail();
        $producto = $menu->productos->firstWhere('id', $data['producto_id']);

        if (!$producto) {
            return response()->json(['error' => 'El producto no está en el menú actual'], 400);
        }

        $precio = $producto->pivot->precio;
        $subtotal = $data['cantidad'] * $precio;

        $detalle = PedidoDetalle::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $precio,
            'subtotal' => $subtotal,
        ]);

        $pedido->total += $subtotal;
        $pedido->save();

        return response()->json(['mensaje' => 'Producto agregado', 'detalle' => $detalle], 201);
    }

    public function quitarProducto($pedidoId, $productoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        if (!in_array($pedido->estado, ['pendiente', 'en_proceso'])) {
            return response()->json(['error' => 'No se puede modificar un pedido en estado ' . $pedido->estado], 403);
        }

        $detalle = PedidoDetalle::where('pedido_id', $pedidoId)
            ->where('producto_id', $productoId)
            ->firstOrFail();

        $pedido->total -= $detalle->subtotal;
        $pedido->save();

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
