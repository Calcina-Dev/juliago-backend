<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Validar que tenga empresa y que esté activa (excepto si es superadmin)
        if ($user->rol !== 'superadmin') {
            if (!$user->empresa || !$user->empresa->activa) {
                return response()->json([
                    'message' => 'La empresa está inactiva o no existe.'
                ], 403);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'rol' => $user->rol,
            'usuario' => $user,
            'empresa' => $user->empresa, // 👈 se devuelve la empresa
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user()
        ]);
    }

}
