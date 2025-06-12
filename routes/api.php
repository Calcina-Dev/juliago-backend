<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ProductoController,
    MesaController,
    PedidoController,
    PagoController,
    DashboardController,
    AuthController,
    MenuController
};

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);  // Registro cliente

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    // --- Exclusivas ADMIN ---
    Route::middleware('rol:admin')->group(function () {
       
        // Menús completos y gestión especial
        Route::apiResource('menus', MenuController::class);
        Route::post('menus/{id}/activar', [MenuController::class, 'activar']);
        Route::get('productos/precios-recomendados', [MenuController::class, 'preciosRecomendados']);

         // Productos y mesas con CRUD completo
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('mesas', MesaController::class);


        // Cierres generales y dashboard completo
        Route::get('cierres/general', [PagoController::class, 'resumenGeneral']);
        Route::get('dashboard/dia', [DashboardController::class, 'resumenDiario']);
        Route::get('dashboard/semana', [DashboardController::class, 'resumenSemanal']);
        Route::get('dashboard/productos-mas-vendidos', [DashboardController::class, 'productosMasVendidos']);
        Route::get('dashboard/estadisticas-pedidos', [DashboardController::class, 'estadisticasPedidos']);
        Route::get('dashboard/ventas-por-dia-semana', [DashboardController::class, 'ventasPorDiaSemana']);
        Route::get('dashboard/ventas-mensuales', [DashboardController::class, 'ventasMensuales']);
        Route::get('dashboard/categorias-mas-vendidas', [DashboardController::class, 'categoriasMasVendidas']);
        Route::get('dashboard/ventas-por-hora', [DashboardController::class, 'ventasPorHora']);
        Route::get('dashboard/revenue-comparativo', [DashboardController::class, 'revenueSemanalComparativo']);
    });

    // --- Exclusivas MESERO ---
    Route::middleware('rol:mesero,admin')->group(function () {
        Route::post('pedidos/crear', [PedidoController::class, 'store']);
        Route::put('pedidos/{id}/cambiar-estado', [PedidoController::class, 'cambiarEstado']);
        Route::delete('pedidos/{id}/cancelar', [PedidoController::class, 'destroy']);
        Route::post('pedidos/{id}/agregar-producto', [PedidoController::class, 'agregarProducto']);
        Route::delete('pedidos/{id}/producto/{producto_id}/quitar', [PedidoController::class, 'quitarProducto']);
        Route::get('pedidos', [PedidoController::class, 'index']);
        Route::get('pedidos/{id}', [PedidoController::class, 'show']);
        Route::get('mesas/{id}/pedidos', [PedidoController::class, 'porMesa']);
        
    });

    // --- Exclusivas COCINERO ---
    Route::middleware('rol:cocinero,admin')->group(function () {
        Route::put('pedidos/{id}/cambiar-estado', [PedidoController::class, 'cambiarEstado']);
    });

    // --- Exclusivas CAJERO ---
    Route::middleware('rol:cajero,admin')->group(function () {
        Route::post('pagos/registrar', [PagoController::class, 'store']);
        Route::get('pagos/metodos', [PagoController::class, 'metodosPago']);
        Route::get('cierres/diario', [PagoController::class, 'resumenDiario']);
    });

    // --- Exclusivas CLIENTE ---
    Route::middleware('rol:cliente,admin')->group(function () {
        Route::get('menus/actual', [MenuController::class, 'menuActualCliente']);
        Route::get('productos/cliente', [ProductoController::class, 'indexCliente']);
        Route::post('pedidos/cliente', [PedidoController::class, 'storeCliente']);
        Route::get('pedidos/cliente', [PedidoController::class, 'indexCliente']);
        Route::get('pedidos/cliente/{id}', [PedidoController::class, 'showCliente']);
        Route::put('perfil/cliente', [AuthController::class, 'updatePerfilCliente']);
    });

    // --- Rutas compartidas para varios roles (incluye admin) ---
    Route::middleware('rol:admin,mesero,cocinero')->group(function () {
        Route::get('pedidos/activos', [PedidoController::class, 'activos']);
    });

    Route::middleware('rol:admin,mesero,cocinero,cliente')->group(function () {
        Route::get('pedidos/{id}/estados-validos', [PedidoController::class, 'estadosValidos']);
        Route::get('pedidos/estados', [PedidoController::class, 'todosLosEstados']);
        Route::get('pedidos/{id}/historial', [PedidoController::class, 'historial']);
    });

    
});
