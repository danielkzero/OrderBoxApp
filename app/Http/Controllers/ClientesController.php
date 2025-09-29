<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientesController extends Controller
{
    public function index()
    {
        $clientes = Clientes::with('telefones', 'emails', 'enderecos')->get();
        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes
        ]);
    }
}
