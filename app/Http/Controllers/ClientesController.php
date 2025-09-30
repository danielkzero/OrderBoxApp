<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Pega todos os clientes da empresa com relacionamentos
        $clientes = Clientes::with(['telefones', 'emails', 'enderecos', 'pedidos'])
            ->where('empresa_id', $empresa)
            ->get();

        // Inicializa contadores
        $ativos = 0;
        $inativos_recentes = 0;
        $inativos_antigos = 0;
        $prospects = 0;

        foreach ($clientes as $cliente) {
            // Pega a data do último pedido do cliente
            $ultimoPedido = $cliente->pedidos->sortByDesc('created_at')->first();

            if (!$ultimoPedido) {
                $prospects++;
                $cliente->status = 'prospect';
                continue;
            }

            $mesesDesdeUltimoPedido = Carbon::parse($ultimoPedido->created_at)->diffInMonths(now());

            if ($mesesDesdeUltimoPedido < 6) {
                $ativos++;
                $cliente->status = 'ativo';
            } elseif ($mesesDesdeUltimoPedido >= 6 && $mesesDesdeUltimoPedido < 12) {
                $inativos_recentes++;
                $cliente->status = 'inativo_recente';
            } else {
                $inativos_antigos++;
                $cliente->status = 'inativo_antigo';
            }
        }

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'chartData' => [
                'series' => [$ativos, $inativos_recentes, $inativos_antigos, $prospects],
                'counts' => [
                    'ativos' => $ativos,
                    'inativos_recentes' => $inativos_recentes,
                    'inativos_antigos' => $inativos_antigos,
                    'prospects' => $prospects,
                ],
            ],
            'empresa' => $empresa,
        ]);
    }
}
