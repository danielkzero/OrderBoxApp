<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return inertia('Welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('{empresa}/', function ($empresa) { return inertia('Pedidos/Index'); })->where('empresa', '[0-9]+');

    Route::resource('{empresa}/pedidos', App\Http\Controllers\PedidosController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('{empresa}/clientes', App\Http\Controllers\ClientesController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('{empresa}/produtos', App\Http\Controllers\ProdutosController::class)->where(['empresa' => '[0-9]+']);
    Route::resource('{empresa}/dashboard', App\Http\Controllers\DashboardController::class)->where(['empresa' => '[0-9]+']);
});
