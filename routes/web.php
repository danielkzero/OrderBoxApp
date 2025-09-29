<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return inertia('Pedidos/Index'); // página inicial logada
    });

    Route::resource('pedidos', App\Http\Controllers\PedidosController::class);
    Route::resource('clientes', App\Http\Controllers\ClientesController::class);
    Route::resource('empresas', App\Http\Controllers\EmpresasController::class);
    Route::resource('produtos', App\Http\Controllers\ProdutosController::class);
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
