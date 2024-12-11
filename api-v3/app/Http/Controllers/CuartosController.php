<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuarto;
use App\Models\User;
use App\Models\Sensor;
use App\Models\Actuador;

class CuartosController extends Controller
{
    public function index()
    {
        $cuartos = Cuarto::all();
        return response()->json($cuartos);
    }

    public function show()
    {
        $cuarto = Cuarto::select('numCuarto', 'nombre', 'ubicacion')->first();
        return response()->json($cuarto);
    }


    
}
