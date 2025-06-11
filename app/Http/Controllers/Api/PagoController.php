<?php

namespace App\Http\Controllers\Api;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    private const METODOS_VALIDOS = [
        'efectivo' => 'Efectivo',
        'tarjeta'  => 'Tarjeta',
        'yape'     => 'Yape',
        'plin'     => 'Plin',
        'transferencia' => 'Transferencia',
    ];

  public function store(Request $request)
{
    $request->validate([
        'pedido_id'    => 'required|exists:pedidos,id',
        'monto'        => 'required|numeric|min:0.01',
        'metodo_pago'  => ['required', 'string', Rule::in(array_keys(self::METODOS_VALIDOS))],
    ]);

    DB::beginTransaction();
    try {
        $pedido = Pedido::withTrashed()->findOrFail($request->pedido_id);

        if ($pedido->trashed()) {
            return response()->json(['mensaje' => 'No se puede pagar un pedido cancelado'], 400);
        }

        if (in_array($pedido->estado, ['pagado', 'cerrado'])) {
            return response()->json(['mensaje' => 'Este pedido ya está pagado o cerrado'], 400);
        }

        Pago::create([
            'pedido_id'   => $pedido->id,
            'usuario_id'  => auth()->id(), // ✅ Cajero responsable
            'monto'       => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'pagado_en'   => now(), // ✅ nombre estandarizado
        ]);

        $pedido->update(['estado' => 'pagado']);

        DB::commit();
        return response()->json(['mensaje' => 'Pago registrado correctamente']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function resumenDiario(Request $request)
{
    $fecha = $request->get('fecha', now()->toDateString());

    $pagos = Pago::whereDate('fecha_pago', $fecha)
        ->where('usuario_id', auth()->id())
        ->get();

    $total = $pagos->sum('monto');

    $porMetodo = $pagos->groupBy('metodo_pago')->map(function ($items) {
        return [
            'cantidad' => $items->count(),
            'total' => $items->sum('monto'),
        ];
    });

    return response()->json([
        'fecha' => $fecha,
        'cajero' => auth()->user()->name,
        'total_recaudado' => $total,
        'detalle_por_metodo' => $porMetodo,
        'pagos' => $pagos,
    ]);
}


   public function resumenGeneral(Request $request)
{
    $fecha = $request->get('fecha', now()->toDateString());

    $pagos = Pago::with('usuario')
        ->whereDate('fecha_pago', $fecha)
        ->get();

    $total = $pagos->sum('monto');

    $porUsuario = $pagos->groupBy('usuario.name')->map(function ($items) {
        return [
            'cantidad' => $items->count(),
            'total' => $items->sum('monto'),
            'por_metodo' => $items->groupBy('metodo_pago')->map(fn($g) => $g->sum('monto')),
        ];
    });

    return response()->json([
        'fecha' => $fecha,
        'total_general' => $total,
        'detalle_por_usuario' => $porUsuario,
    ]);
}


    public function metodosPago()
    {
        $respuesta = collect(self::METODOS_VALIDOS)->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values();

        return response()->json(['metodos' => $respuesta]);
    }
}
