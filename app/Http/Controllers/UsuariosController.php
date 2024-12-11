<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cuarto;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    public function index()
    {
        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }
        return response()->json($cuarto->usuarios);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string',
        ]);

        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $usuario = $cuarto->usuarios()->where('correo', $credentials['correo'])->first();

        if (!$usuario || !password_verify($credentials['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        return response()->json([
            'message' => 'Login exitoso',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'correo' => 'required|email|unique:users,correo',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:10',
        ]);

        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $validated['password'] = bcrypt($validated['password']);
        $validated['fecha_registro'] = now()->format('Y-m-d H:i:s');

        $usuario = $cuarto->usuarios()->create($validated);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    public function logout()
    {
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'correo' => 'required|email',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:10',
        ]);

        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $validated['password'] = bcrypt($validated['password']);
        $validated['fecha_registro'] = now()->format('Y-m-d H:i:s');

        // Crear el usuario embebido en el cuarto
        $usuario = $cuarto->usuarios()->create($validated);

        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $usuario = $cuarto->usuarios()->where('_id', $id)->first();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $usuario = $cuarto->usuarios()->where('_id', $id)->first();
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'ap_paterno' => 'sometimes|required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'correo' => 'sometimes|required|email',
            'password' => 'sometimes|required|string|min:6',
            'telefono' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $cuarto->usuarios()->where('_id', $id)->update($validated);
        return response()->json(['message' => 'Usuario actualizado exitosamente']);
    }

    public function destroy($id)
    {
        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        // Usar pull para eliminar el usuario del array usuarios
        $cuarto->pull('usuarios', ['_id' => $id]);

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
