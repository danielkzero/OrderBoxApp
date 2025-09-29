<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use App\Models\Empresas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidosController extends Controller
{
    public function index()
    {
        $pedidos = Pedidos::with(['itens'])->get();
        $empresas = Empresas::get();
        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'empresas' => $empresas
        ]);
    }
}
