<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ProductoController,
    MesaController,
    PedidoController,
    PagoController,
    DashboardController,
    AuthController
};

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    // ADMIN - acceso total
    Route::middleware('rol:admin')->group(function () {
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('mesas', MesaController::class);
        Route::get('dashboard/dia', [DashboardController::class, 'resumenDiario']);
        Route::get('dashboard/semana', [DashboardController::class, 'resumenSemanal']);
        Route::get('dashboard/productos-mas-vendidos', [DashboardController::class, 'productosMasVendidos']);
        Route::get('cierres', [PagoController::class, 'resumenDiario']);
        Route::get('pedidos/{id}/historial', [PedidoController::class, 'historial']);
        Route::get('cierres/general', [PagoController::class, 'resumenGeneral']);

        Route::get('dashboard/estadisticas-pedidos', [DashboardController::class, 'estadisticasPedidos']);
        Route::get('dashboard/ventas-por-dia-semana', [DashboardController::class, 'ventasPorDiaSemana']);
        Route::get('dashboard/ventas-mensuales', [DashboardController::class, 'ventasMensuales']);

        Route::get('dashboard/categorias-mas-vendidas', [DashboardController::class, 'categoriasMasVendidas']);
        Route::get('dashboard/ventas-por-hora', [DashboardController::class, 'ventasPorHora']);

        Route::get('dashboard/revenue-comparativo', [DashboardController::class, 'revenueSemanalComparativo']);


    });

    // MESERO - puede crear y gestionar pedidos
    Route::middleware('rol:mesero')->group(function () {
        Route::post('pedidos', [PedidoController::class, 'store']);
        Route::get('pedidos', [PedidoController::class, 'index']);
        Route::get('pedidos/activos', [PedidoController::class, 'activos']);
        Route::put('pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado']);
        Route::delete('pedidos/{id}', [PedidoController::class, 'destroy']);
        Route::get('pedidos/{id}', [PedidoController::class, 'show']);
        Route::post('pedidos/{id}/agregar-producto', [PedidoController::class, 'agregarProducto']);
        Route::delete('pedidos/{id}/producto/{producto_id}', [PedidoController::class, 'quitarProducto']);
        Route::get('pedidos/{id}/estados-validos', [PedidoController::class, 'estadosValidos']);
        Route::get('pedidos/estados', [PedidoController::class, 'todosLosEstados']);
        Route::get('mesas/{id}/pedidos', [PedidoController::class, 'porMesa']);
        Route::get('pedidos/{id}/historial', [PedidoController::class, 'historial']);

    });

    // COCINERO - solo ve y actualiza estados de pedidos
    Route::middleware('rol:cocinero')->group(function () {
        Route::get('pedidos/activos', [PedidoController::class, 'activos']);
        Route::put('pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado']);
    });

    // CAJERO - gestiona pagos
    Route::middleware('rol:cajero')->group(function () {
        Route::post('pagos', [PagoController::class, 'store']);
        Route::get('pagos/metodos', [PagoController::class, 'metodosPago']);
        Route::get('cierres', [PagoController::class, 'resumenDiario']);
    });
});
