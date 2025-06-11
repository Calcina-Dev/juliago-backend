<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoDetalle;
use App\Models\Pedido;


class DashboardController extends Controller
{
    
   public function ventasPorDiaSemana(Request $request)
    {
        $inicio = $request->get('inicio', now()->subDays(6)->startOfDay());
        $fin = $request->get('fin', now()->endOfDay());

        $ventas = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->selectRaw('EXTRACT(DOW FROM pagado_en) AS dia_semana, SUM(monto) as total')
            ->groupBy('dia_semana')
            ->orderBy('dia_semana')
            ->get();

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $resultado = array_fill_keys($dias, 0);

        foreach ($ventas as $v) {
            $index = intval($v->dia_semana);
            $resultado[$dias[$index]] = floatval($v->total);
        }

        return response()->json([
            'desde' => $inicio,
            'hasta' => $fin,
            'ventas_por_dia' => $resultado
        ]);
    }



  public function ventasMensuales(Request $request)
    {
        $inicio = $request->get('inicio');
        $fin = $request->get('fin');
        $año = $request->get('year');

        $query = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->selectRaw('EXTRACT(MONTH FROM pagado_en) AS mes, SUM(monto) AS total');

        if ($inicio && $fin) {
            $query->whereBetween('pagado_en', [$inicio, $fin]);
        } elseif ($año) {
            $query->whereYear('pagado_en', $año);
        }

        $ventas = $query->groupBy('mes')->orderBy('mes')->get();

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $resultado = array_fill_keys(array_values($meses), 0);

        foreach ($ventas as $v) {
            $nombreMes = $meses[intval($v->mes)];
            $resultado[$nombreMes] = floatval($v->total);
        }

        return response()->json([
            'filtro' => $inicio && $fin ? [$inicio, $fin] : $año,
            'ventas_por_mes' => $resultado
        ]);
    }




    
    public function resumenDiario(Request $request)
    {
        $inicio = Carbon::parse($request->get('inicio', now()->startOfDay()));
        $fin = Carbon::parse($request->get('fin', now()->endOfDay()));

        $pagos = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->select('pagos.*')
            ->get();

        $total = $pagos->sum('monto');
        $porMetodo = $pagos->groupBy('metodo_pago')->map(fn($items) => $items->sum('monto'));
        $numPedidos = $pagos->count();
        $promedio = $numPedidos > 0 ? round($total / $numPedidos, 2) : 0;

        return response()->json([
            'desde' => $inicio->toDateTimeString(),
            'hasta' => $fin->toDateTimeString(),
            'total_recaudado' => $total,
            'num_pedidos' => $numPedidos,
            'promedio_por_pedido' => $promedio,
            'detalle_por_metodo' => $porMetodo,
        ]);
    }


   public function resumenSemanal(Request $request)
    {
        $inicio = Carbon::parse($request->get('inicio', now()->startOfWeek()));
        $fin = Carbon::parse($request->get('fin', now()->endOfWeek()));

        $resumen = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->select(
                DB::raw("to_char(pagado_en, 'Day') as dia"),
                DB::raw("sum(monto) as total")
            )
            ->groupBy(DB::raw("to_char(pagado_en, 'Day')"))
            ->orderByRaw("min(pagado_en)")
            ->get();

        return response()->json([
            'semana_del' => $inicio->toDateString(),
            'semana_al' => $fin->toDateString(),
            'resumen' => $resumen
        ]);
    }

  

  public function productosMasVendidos(Request $request)
{
    $inicio = $request->get('inicio');
    $fin = $request->get('fin');
    $ordenarPor = $request->get('ordenar_por', 'unidades'); // 'unidades' o 'ventas'

    $query = PedidoDetalle::select(
            'producto_id',
            DB::raw('SUM(cantidad) as total_unidades'),
            DB::raw('SUM(precio_unitario * cantidad) as total_ventas')
        )
        ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
        ->join('productos', 'pedido_detalles.producto_id', '=', 'productos.id')
        ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
        ->whereNull('pedidos.deleted_at')
        ->groupBy('producto_id', 'productos.nombre', 'categorias.nombre')
        ->orderByDesc(
            $ordenarPor === 'ventas'
                ? DB::raw('SUM(precio_unitario * cantidad)')
                : DB::raw('SUM(cantidad)')
        )
        ->limit(5);

    if ($inicio && $fin) {
        $query->whereBetween('pedido_detalles.created_at', [$inicio, $fin]);
    }

    $ranking = $query->get()->map(function ($item) {
        return [
            'producto'       => $item->producto->nombre,
            'categoria'      => $item->producto->categoria->nombre,
            'total_unidades' => $item->total_unidades,
            'total_ventas'   => round($item->total_ventas, 2),
        ];
    });

    return response()->json($ranking);
}


public function estadisticasPedidos(Request $request)
{
    $fecha = $request->get('fecha', now()->toDateString()); // por defecto hoy, formato 'YYYY-MM-DD'

    $inicio = Carbon::parse($fecha)->startOfDay();
    $fin = Carbon::parse($fecha)->endOfDay();

    $conteo = Pedido::whereBetween('created_at', [$inicio, $fin])
        ->selectRaw('estado, count(*) as cantidad')
        ->groupBy('estado')
        ->pluck('cantidad', 'estado')
        ->toArray();

    $estados = ['pendiente', 'en_proceso', 'servido', 'pagado', 'cerrado', 'cancelado'];

    foreach ($estados as $estado) {
        $conteo[$estado] = $conteo[$estado] ?? 0;
    }

    $total = array_sum($conteo);

    return response()->json([
        'fecha' => $fecha,
        'total_pedidos' => $total,
        'por_estado' => $conteo,
    ]);
}




public function categoriasMasVendidas(Request $request)
{
    $inicio = $request->get('inicio');
    $fin = $request->get('fin');

    $query = DB::table('pedido_detalles')
        ->join('productos', 'pedido_detalles.producto_id', '=', 'productos.id')
        ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
        ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
        ->whereNull('pedidos.deleted_at')
        ->select(
            'categorias.nombre as categoria',
            DB::raw('SUM(pedido_detalles.cantidad) as total_unidades')
        )
        ->groupBy('categorias.nombre');

    if ($inicio && $fin) {
        $query->whereBetween('pedido_detalles.created_at', [$inicio, $fin]);
    }

    $categorias = $query->get();

    $totalGeneral = $categorias->sum('total_unidades');

    $resultado = $categorias->map(function ($item) use ($totalGeneral) {
        $porcentaje = $totalGeneral > 0
            ? round(($item->total_unidades / $totalGeneral) * 100)
            : 0;

        return [
            'categoria'  => $item->categoria,
            'porcentaje' => $porcentaje,
        ];
    });

    return response()->json($resultado);
}


    public function ventasPorHora(Request $request)
    {
        $inicio = $request->get('inicio', now()->startOfDay());
        $fin = $request->get('fin', now()->endOfDay());

        $pagos = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->select('pagos.*')
            ->get();

        $ventas = $pagos->groupBy(function ($pago) {
            $hora = Carbon::parse($pago->pagado_en)->hour;
            if ($hora >= 6 && $hora < 12) return 'mañana';
            if ($hora >= 12 && $hora < 18) return 'tarde';
            return 'noche';
        });

        $resultado = [
            'mañana' => ['ventas' => 0, 'monto' => 0],
            'tarde' => ['ventas' => 0, 'monto' => 0],
            'noche' => ['ventas' => 0, 'monto' => 0],
        ];

        foreach ($ventas as $franja => $pagos) {
            $resultado[$franja]['ventas'] = $pagos->count();
            $resultado[$franja]['monto'] = $pagos->sum('monto');
        }

        return response()->json($resultado);
    }


    public function revenueSemanalComparativo()
{
    $hoy = Carbon::today();
    $inicioActual = $hoy->copy()->startOfWeek(); // Lunes actual
    $finActual = $inicioActual->copy()->endOfWeek(); // Domingo actual

    $inicioAnterior = $inicioActual->copy()->subWeek();
    $finAnterior = $finActual->copy()->subWeek();

    // Helper para calcular ventas por día
    $ventasPorDia = function ($inicio, $fin) {
        $result = DB::table('pagos')
            ->join('pedidos', 'pagos.pedido_id', '=', 'pedidos.id')
            ->whereBetween('pagado_en', [$inicio, $fin])
            ->whereNull('pedidos.deleted_at')
            ->where('pedidos.estado', '!=', 'cancelado')
            ->selectRaw('EXTRACT(DOW FROM pagado_en) AS dia, SUM(monto) AS total')
            ->groupByRaw('EXTRACT(DOW FROM pagado_en)')
            ->pluck('total', 'dia');

        // Estructura con valores por defecto
        $dias = [1, 2, 3, 4, 5, 6, 0]; // Lunes a Domingo
        $valores = [];
        foreach ($dias as $d) {
            $valores[] = round(floatval($result[$d] ?? 0), 2);
        }
        return $valores;
    };

    $ventasActual = $ventasPorDia($inicioActual, $finActual);
    $ventasAnterior = $ventasPorDia($inicioAnterior, $finAnterior);

    $totalActual = array_sum($ventasActual);
    $totalAnterior = array_sum($ventasAnterior);

    $variacion = $totalAnterior > 0
        ? round((($totalActual - $totalAnterior) / $totalAnterior) * 100, 2)
        : null;

    return response()->json([
        'semana_actual' => [
            'desde' => $inicioActual->toDateString(),
            'hasta' => $finActual->toDateString(),
            'total' => $totalActual,
            'por_dia' => $ventasActual
        ],
        'semana_anterior' => [
            'desde' => $inicioAnterior->toDateString(),
            'hasta' => $finAnterior->toDateString(),
            'total' => $totalAnterior,
            'por_dia' => $ventasAnterior
        ],
        'variacion_porcentual' => $variacion
    ]);
}


}
