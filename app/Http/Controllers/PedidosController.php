<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use App\Models\Pedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        $pedidos = Pedidos::with(['itens', 'cliente', 'usuario'])
            ->where('empresa_id', $empresa)
            ->get()
            ->map(function ($pedido) {
                // soma os subtotais dos itens desse pedido
                $pedido->total = $pedido->itens->sum('subtotal');
                return $pedido;
            });

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'empresa_id' => $empresa
        ]);
    }
}
