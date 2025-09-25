<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidosController extends Controller
{
    public function index()
    {
        $pedidos = Pedidos::with(['itens'])->get();
        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos
        ]);
    }
}
