<?php
namespace App\Http\Controllers\Api;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio,
                    'descripcion' => $producto->descripcion,
                    'categoria_id' => $producto->categoria_id,
                    'categoria_nombre' => optional($producto->categoria)->nombre,
                    'imagen_url' => $producto->imagen_url ?? null,
                    'activo' => $producto->activo ?? true,
                    'created_at' => $producto->created_at,
                    'updated_at' => $producto->updated_at,
                ];
            });

        return response()->json($productos);
    }
    public function store(Request $request)
    {
        $producto = Producto::create($request->all());
        return response()->json($producto, 201);
    }

    public function show($id)
    {
        return Producto::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        return response()->json($producto);
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete(); // esto hace soft delete
        return response()->json(['mensaje' => 'Producto eliminado (soft delete)']);
    }
}
