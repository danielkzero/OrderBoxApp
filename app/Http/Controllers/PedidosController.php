<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use App\Models\Empresas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PedidosController extends Controller
{
    public function index($empresa)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuário não logado.');
        }

        // Pega os IDs das empresas que o usuário está associado
        $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();

        if (!in_array($empresa, $empresaIds)) {
            abort(403, 'Empresa inválida.');
        }

        // Pega os pedidos apenas dessas empresas
        $pedidos = Pedidos::with(['itens'])
            ->whereIn('empresa_id', $empresa)
            ->get();

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
        ]);
    }
}
