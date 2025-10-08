<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\DashboardController;

// Página inicial
Route::get('/', function () {
    return inertia('Welcome');
});

// Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas autenticadas
Route::prefix('{empresa}')->middleware('auth')->group(function () {

    // Página inicial da empresa (lista de pedidos)
    Route::get('', function ($empresa) {
        return inertia('Pedidos/Index');
    })->where('empresa', '[0-9]+');

    // Recursos principais
    Route::resource('pedidos', PedidosController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('clientes', ClientesController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('produtos', ProdutosController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('dashboard', DashboardController::class)->where(['empresa' => '[0-9]+']);
});
