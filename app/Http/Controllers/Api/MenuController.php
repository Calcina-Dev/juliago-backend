<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\Producto;

class MenuController extends Controller
{
    // Listar todos los menús con sus productos
    public function index()
    {
        $menus = Menu::with('productos')->get();
        return response()->json($menus);
    }

    // Crear un nuevo menú con productos y precios personalizados
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $menu = Menu::create([
                'nombre' => $data['nombre'],
                'es_actual' => false,
            ]);

            foreach ($data['productos'] as $producto) {
                $menu->productos()->attach($producto['id'], ['precio' => $producto['precio']]);
            }

            DB::commit();

            return response()->json($menu->load('productos'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear menú', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Mostrar un menú específico con sus productos
    public function show($id)
    {
        $menu = Menu::with('productos')->findOrFail($id);
        return response()->json($menu);
    }

    // Actualizar un menú y sus productos con precios
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $menu->update(['nombre' => $data['nombre']]);

            // Sincronizar productos y precios
            $syncData = [];
            foreach ($data['productos'] as $producto) {
                $syncData[$producto['id']] = ['precio' => $producto['precio']];
            }
            $menu->productos()->sync($syncData);

            DB::commit();

            return response()->json($menu->load('productos'), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar menú', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Activar un menú (solo uno puede estar activo)
    public function activar($id)
    {
        DB::beginTransaction();

        try {
            // Desactivar todos los menús activos
            Menu::where('es_actual', true)->update(['es_actual' => false]);

            // Activar el menú seleccionado
            $menu = Menu::findOrFail($id);
            $menu->es_actual = true;
            $menu->save();

            DB::commit();

            return response()->json(['mensaje' => 'Menú activado', 'menu' => $menu]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al activar menú', 'mensaje' => $e->getMessage()], 500);
        }
    }

    // Eliminar menú (soft delete)
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        if ($menu->es_actual) {
            return response()->json(['error' => 'No se puede eliminar el menú activo'], 400);
        }

        $menu->delete();

        return response()->json(['mensaje' => 'Menú eliminado']);
    }

    // Obtener precios recomendados de productos (para creación/edición de menú)
    public function preciosRecomendados()
    {
        $productos = Producto::select('id', 'nombre', 'precio')->get();

        return response()->json($productos);
    }
}
