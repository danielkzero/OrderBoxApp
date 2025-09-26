<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::resource('pedidos', App\Http\Controllers\PedidosController::class);
Route::resource('clientes', App\Http\Controllers\ClientesController::class);
