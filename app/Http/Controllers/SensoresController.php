<?php

namespace App\Http\Controllers;

use App\Models\Cuarto;
use App\Models\Sensor;
use Illuminate\Http\Request;

class SensoresController extends Controller
{
    public function index()
    {
        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }
        return response()->json($cuarto->sensores);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'fecha' => 'nullable|date',
        ]);

        $sensor = Sensor::create($validated);
        return response()->json($sensor, 201);
    }

    public function show($id)
    {
        $sensor = Sensor::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor no encontrado'], 404);
        }
        return response()->json($sensor);
    }

    public function update(Request $request, $id)
    {
        $sensor = Sensor::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'tipo' => 'sometimes|required|string|max:255',
            'valor' => 'sometimes|required|numeric',
            'fecha' => 'nullable|date',
        ]);

        $sensor->update($validated);
        return response()->json($sensor);
    }

    public function destroy($id)
    {
        $sensor = Sensor::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor no encontrado'], 404);
        }

        $sensor->delete();
        return response()->json(['message' => 'Sensor eliminado exitosamente']);
    }
}