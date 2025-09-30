<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ClientesController extends Controller
{
    public function index($empresa)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuário não logado.');
        }

        // Pega os IDs das empresas do usuário
         // Pega os IDs das empresas que o usuário tem acesso
        $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();

        if (!in_array($empresa, $empresaIds)) {
            abort(403, 'Empresa inválida.');
        }

        // Filtra apenas os clientes dessas empresas
        $clientes = Clientes::with(['telefones', 'emails', 'enderecos'])
            ->where('empresa_id', $empresa)
            ->get();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes
        ]);
    }
}
