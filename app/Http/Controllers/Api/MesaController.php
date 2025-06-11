<?php

namespace App\Http\Controllers\Api;

use App\Models\Mesa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MesaController extends Controller
{
    public function index()
    {
        return Mesa::all();
    }

    public function store(Request $request)
    {
        $mesa = Mesa::create($request->all());
        return response()->json($mesa, 201);
    }

    public function show($id)
    {
        return Mesa::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update($request->all());
        return response()->json($mesa);
    }

    public function destroy($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->delete();
        return response()->json(['mensaje' => 'Mesa eliminada (soft delete)']);
    }
}
