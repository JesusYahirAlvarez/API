<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CuartosController;
use App\Http\Controllers\SensoresController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ActuadoresController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(SensoresController::class)->group(function () {
    Route::get('/sensores','index');
    Route::post('/sensores','store');
    Route::get('/sensores/{id}', 'show');
    Route::put('/sensores/{id}', 'update');
    Route::delete('/sensores/{id}','destroy');
});

Route::controller(ActuadoresController::class)->group(function () {
    Route::get('/actuadores','index');
    Route::post('/actuadores','store');
    Route::get('/actuadores/recientes', 'ultimasFotos');
    Route::get('/actuadores/{id}', 'show');
    Route::put('/actuadores/{id}', 'update');
    Route::delete('/actuadores/{id}','destroy');
    Route::post('/actuadores/foto-manual', 'tomarFotoManual');
    Route::post('/actuadores/tomar-foto-desde-celular', 'tomarFotoDesdeCelular');

});

Route::controller(UsuariosController::class)->group(function () {
    Route::get('/usuarios','index');
    Route::post('/usuarios','store');
    Route::get('/usuarios/{id}', 'show');
    Route::put('/usuarios/{id}', 'update');
    Route::delete('/usuarios/{id}','destroy');
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
});

Route::controller(CuartosController::class)->group(function () {
    #Route::get('/cuartos','index');
    Route::post('/cuartos','store');
    Route::get('/cuartos', 'show');
    Route::put('/cuartos/{id}', 'update');
    Route::delete('/cuartos/{id}','destroy');
});



Route::get('/test-cuarto', function () {
    $cuarto = App\Models\Cuarto::first();
    return response()->json($cuarto);
});