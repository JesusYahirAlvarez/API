<?php

namespace App\Http\Controllers;

use Log;
use Carbon\Carbon;
use App\Models\Cuarto;
use App\Models\Actuador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ActuadoresController extends Controller
{
    public function index()
    {

        $actuadores = Cuarto::first()->actuadores;

        // Transformar cada actuador para cambiar _id a id
        $actuadoresTransformados = $actuadores->map(function ($actuador) {
            $datos = $actuador->toArray();
            $datos['id'] = $datos['_id'];
            unset($datos['_id']);
            return $datos;
        });

        return response()->json($actuadoresTransformados);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'motivo' => 'required|string|max:255',
        ]);

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $timestamp = time();
            $nombreImagen = $timestamp . '_' . $imagen->getClientOriginalName();
            $path = $imagen->storeAs('imagenes-actuadores', $nombreImagen, 'public');
            $validated['imagen'] = asset('storage/' . $path);

            // Establecer la fecha usando Carbon
            $validated['fecha'] = Carbon::now()->toIso8601String();
        } else {
            // Si no hay imagen, también establecer la fecha
            $validated['fecha'] = Carbon::now()->toIso8601String();
        }

        $cuarto = Cuarto::first();
        if (!$cuarto) {
            return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
        }

        $actuador = $cuarto->actuadores()->create($validated);

        return response()->json([
            'message' => 'Actuador creado exitosamente',
            'actuador' => $actuador
        ], 201);
    }
    public function show($id)
    {
        dd("xdd");
    }

    public function update(Request $request, $id)
    {
        $actuador = Actuador::find($id);
        if (!$actuador) {
            return response()->json(['message' => 'Actuador no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'tipo' => 'sometimes|required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'motivo' => 'nullable|string|max:255',
            'fecha' => 'nullable|date',
        ]);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($actuador->imagen) {
                Storage::disk('public')->delete($actuador->imagen);
            }
            $path = $request->file('imagen')->store('imagenes-actuadores', 'public');
            $validated['imagen'] = $path;
        }

        $actuador->update($validated);
        return response()->json($actuador);
    }

    public function destroy($id)
    {
        try {
            // Obtener el primer cuarto
            $cuarto = Cuarto::first();

            if (!$cuarto) {
                return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
            }

            // Encontrar el actuador embebido por su ID
            $actuador = $cuarto->actuadores()->find($id);

            if (!$actuador) {
                return response()->json(['message' => 'Actuador no encontrado'], 404);
            }

            // Guardar la imagen antes de eliminar el actuador
            $imagenActuador = $actuador->imagen ?? null;

            // Eliminar el actuador embebido
            $actuador->delete();

            // Eliminar la imagen física si existe
            if ($imagenActuador) {
                $imagePath = str_replace(asset('storage/'), '', $imagenActuador);
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json(['message' => 'Actuador eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el actuador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Método para guardar la imagen enviada desde Python.
     */


    public function subirImagen(Request $request)
    {
        // Agregar log para depuración

        $validated = $request->validate([
            'nombre' => 'required|string|max:200',
            'tipo' => 'required|string|max:200',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'motivo' => 'required|string|max:255',
            'fecha' => 'nullable|date',
        ]);

        if ($request->hasFile('imagen')) {


            $imagen = $request->file('imagen');

            // Obtener el nombre original del archivo
            $nombreOriginal = $imagen->getClientOriginalName();

            // Opcional: Añadir un prefijo para evitar duplicados (recomendado)
            // $nombreConPrefijo = time() . '_' . $nombreOriginal;

            // Almacenar la imagen con el nombre original
            $path = $imagen->storeAs('imagenes-actuadores', $nombreOriginal, 'public');

            // Generar la URL completa de la imagen
            $url = asset('storage/' . $path);

            $cuarto = Cuarto::first();

            if (!$cuarto) {
                return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
            }

            // Agregar el actuador embebido
            $cuarto->actuadores()->create([
                'nombre' => $validated['nombre'],
                'tipo' => $validated['tipo'],
                'imagen' => $url, // Guardar la URL completa de la imagen
                'motivo' => $validated['motivo'],
                'fecha' => $validated['fecha'] ?? now(),
            ]);

            return response()->json([
                'message' => 'Imagen subida y actuador agregado exitosamente',
                'imagen_url' => $url
            ], 201);
        }


        return response()->json(['message' => 'No se encontró ninguna imagen para subir'], 400);
    }


    public function tomarFotoManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'motivo' => 'required|string|max:255',
            ]);

            // Hacer petición al servidor Python
            $pythonServerUrl = env('PYTHON_SERVER_URL', 'http://localhost:5000');
            $client = new \GuzzleHttp\Client();

            $response = $client->post($pythonServerUrl . '/tomar-foto-manual', [
                'json' => [
                    'motivo' => $validated['motivo']
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                return response()->json([
                    'message' => 'Solicitud de foto manual enviada exitosamente'
                ]);
            }

            return response()->json([
                'message' => 'Error al solicitar la foto'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ultimasFotos()
    {
        // Obtener todos los Cuartos que tienen actuadores
        $cuartos = Cuarto::whereNotNull('actuadores')->get();

        $actuadoresRecientes = collect();

        foreach ($cuartos as $cuarto) {
            $actuadores = $cuarto->actuadores;
            $actuadoresRecientes = $actuadoresRecientes->merge($actuadores);
        }

        // Ordenar todos los actuadores por fecha descendente
        $actuadoresRecientes = $actuadoresRecientes->sortByDesc(function ($actuador) {
            return Carbon::parse($actuador['fecha'])->timestamp;
        })->values();

        // Transformar los actuadores para cambiar _id a id
        $actuadoresTransformados = $actuadoresRecientes->map(function ($actuador) {
            $datos = $actuador->toArray();
            $datos['id'] = $datos['_id'];
            unset($datos['_id']);
            return $datos;
        });

        return response()->json($actuadoresTransformados);
    }

    public function tomarFotoDesdeCelular(Request $request)
    {
        try {
            // Validar la solicitud si es necesario
            $validated = $request->validate([
                'motivo' => 'required|string|max:255',
            ]);

            $motivo = $validated['motivo'];

            // Hacer una solicitud HTTP a la API de la Raspberry Pi
            $raspberryApiUrl = 'http://direccion.ip.de.raspberry:5000/tomar-foto';
            $response = Http::post($raspberryApiUrl, [
                'motivo' => $motivo,
            ]);

            if ($response->successful()) {
                // Guardar la imagen recibida en el storage
                $imagenContenido = $response->body();
                $nombreImagen = 'captura_' . time() . '.jpg';
                Storage::disk('public')->put('imagenes-actuadores/' . $nombreImagen, $imagenContenido);

                $urlImagen = asset('storage/imagenes-actuadores/' . $nombreImagen);

                // Guardar el actuador en la base de datos
                $cuarto = Cuarto::first();
                if (!$cuarto) {
                    return response()->json(['message' => 'No se encontró ningún cuarto'], 404);
                }

                $cuarto->actuadores()->create([
                    'nombre' => 'WebCam',
                    'tipo' => 'CA1',
                    'imagen' => $urlImagen,
                    'motivo' => $motivo,
                    'fecha' => now(),
                ]);

                return response()->json([
                    'message' => 'Foto tomada y actuador guardado exitosamente',
                    'imagen_url' => $urlImagen,
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Error al tomar la foto en la Raspberry Pi',
                    'error' => $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
