<?php
/*

Uso futuro

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PedidosApiController;
use App\Http\Controllers\Api\ClientesApiController;
use App\Http\Controllers\Api\ProdutosApiController;

// Exemplo de rotas futuras (API REST externa)
Route::prefix('v1')->group(function () {
    Route::get('/ping', fn() => response()->json(['status' => 'ok']));

    // Pedidos
    Route::get('/pedidos', [PedidosApiController::class, 'index']);
    Route::post('/pedidos', [PedidosApiController::class, 'store']);

    // Clientes
    Route::get('/clientes', [ClientesApiController::class, 'index']);
    Route::post('/clientes', [ClientesApiController::class, 'store']);

    // Produtos
    Route::get('/produtos', [ProdutosApiController::class, 'index']);
});
*/