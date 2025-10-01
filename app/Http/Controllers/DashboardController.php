<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Clientes;
use App\Models\EmpresasUsers;
use App\Models\Pedidos;
use App\Models\PedidosItens;
use App\Models\Produtos;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index($empresa, Request $request)
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

        $inicio = $request->query('inicio', Carbon::now()->startOfMonth()->toDateString());
        $final = $request->query('final', Carbon::now()->endOfMonth()->toDateString());

        // Totais principais
        $totalPedidos = Pedidos::where('empresa_id', $empresa)->whereBetween('data_emissao', [$inicio, $final])->count();
        $totalClientes = Pedidos::where('empresa_id', $empresa)->whereBetween('data_emissao', [$inicio, $final])->distinct('cliente_id')->count();
        $totalProdutos = PedidosItens::whereHas('pedido', function ($query) use ($empresa, $inicio, $final) {
            $query->where('empresa_id', $empresa)->whereBetween('data_emissao', [$inicio, $final]);
        })
        ->distinct('produto_id')
        ->count('produto_id');

        $totalUsuarios = EmpresasUsers::where('empresa_id', $empresa)->count();
        $receitaTotal = Pedidos::where('empresa_id', $empresa)->whereBetween('data_emissao', [$inicio, $final])->sum('total');

        // Pedidos por status
        $pedidosStatusSeries = [
            Pedidos::where(['status' => 'aprovado', 'empresa_id' => $empresa])->whereBetween('data_emissao', [$inicio, $final])->count(),
            Pedidos::where(['status' => 'pendente', 'empresa_id' => $empresa])->whereBetween('data_emissao', [$inicio, $final])->count(),
            Pedidos::where(['status' => 'cancelado', 'empresa_id' => $empresa])->whereBetween('data_emissao', [$inicio, $final])->count(),
        ];

        // Vendas mensais (últimos 6 meses)
        $vendasMensais = Pedidos::selectRaw('MONTH(data_emissao) as mes, SUM(total) as total')
            ->where('empresa_id', $empresa)
            ->whereBetween('data_emissao', [$inicio, $final])
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total')
            ->toArray();

        $meses = Pedidos::selectRaw('MONTH(data_emissao) as mes')
            ->where('empresa_id', $empresa)
            ->whereBetween('data_emissao', [$inicio, $final])
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('mes')
            ->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))
            ->toArray();

        // Produtos mais vendidos
        $produtosMaisVendidos = DB::table('pedidos_itens')
            ->where('pedidos_itens.empresa_id', $empresa)
            ->join('produtos', 'pedidos_itens.produto_id', '=', 'produtos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_itens.pedido_id')
            ->whereBetween('pedidos.data_emissao', [$inicio, $final])
            ->select('produtos.nome', DB::raw('SUM(pedidos_itens.quantidade) as qtd'))
            ->groupBy('produtos.nome')
            ->orderByDesc('qtd')
            ->limit(10)
            ->get();

        $produtosNomes = $produtosMaisVendidos->pluck('nome');
        $produtosQtd = $produtosMaisVendidos->pluck('qtd');

        // Top usuários (quem mais vendeu)
        $topUsuarios = DB::table('pedidos')
            ->where('pedidos.empresa_id', $empresa)
            ->whereBetween('pedidos.data_emissao', [$inicio, $final])
            ->join('users', 'pedidos.criador_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(pedidos.id) as vendas'), DB::raw('SUM(pedidos.total) as valor'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('valor')
            ->limit(5)
            ->get()
            ->map(fn($u) => [
                'nome' => $u->name,
                'vendas' => $u->vendas,
                'valor' => number_format($u->valor, 2, ',', '.')
            ]);

        // Vendas recentes
        $vendasRecentes = Pedidos::with('cliente')
            ->where('pedidos.empresa_id', $empresa)
            ->whereBetween('pedidos.data_emissao', [$inicio, $final])
            ->orderByDesc('data_emissao')
            ->limit(5)
            ->get(['id', 'cliente_id', 'total', 'data_emissao'])
            ->map(fn($p) => [
                'pedido' => $p->id,
                'cliente' => $p->cliente?->razao_social ?? 'Cliente',
                'valor' => number_format($p->total, 2, ',', '.'),
                'data' => $p->data_emissao?->format('d/m/Y')
            ]);

        // Produtos mais recomprados
        $produtosRecompra = DB::table('pedidos_itens')
            ->where('pedidos_itens.empresa_id', $empresa)
            ->join('produtos', 'pedidos_itens.produto_id', '=', 'produtos.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_itens.pedido_id')
            ->whereBetween('data_emissao', [$inicio, $final])
            ->select('produtos.nome', DB::raw('COUNT(pedidos_itens.id) as recompras'))
            ->groupBy('produtos.nome')
            ->orderByDesc('recompras')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'nome' => $p->nome,
                'recompras' => $p->recompras
            ]);

        // Top regiões
        $topRegioes = DB::table('pedidos')
            ->where('pedidos.empresa_id', $empresa)
            ->whereBetween('pedidos.data_emissao', [$inicio, $final])
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->join('cidades_ibge', 'clientes.municipio_codigo', '=', 'cidades_ibge.municipio_codigo')
            ->select('cidades_ibge.municipio_nome as cidade', DB::raw('COUNT(pedidos.id) as vendas'))
            ->groupBy('cidades_ibge.municipio_nome')
            ->orderByDesc('vendas')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'nome' => $r->cidade,
                'vendas' => $r->vendas
            ]);

        // Top categorias (quem mais vendeu)
        $topCategorias = DB::table('pedidos_itens')
            ->where('pedidos_itens.empresa_id', $empresa)
            ->join('produtos', 'pedidos_itens.produto_id', '=', 'produtos.id')
            ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')
            ->join('pedidos', 'pedidos.id', '=', 'pedidos_itens.pedido_id')
            ->whereBetween('pedidos.data_emissao', [$inicio, $final])
            ->select(
                'categorias.nome',
                DB::raw('SUM(pedidos_itens.quantidade) as vendas'),
                DB::raw('SUM(pedidos_itens.quantidade * pedidos_itens.preco_liquido) as valor')
            )
            ->groupBy('categorias.nome')
            ->orderByDesc('vendas')
            ->limit(5)
            ->get()
            ->map(function ($cat) {
                return [
                    'nome' => $cat->nome,
                    'vendas' => $cat->vendas,
                    'valor' => number_format($cat->valor, 2, ',', '.'),
                    'variacao' => rand(-15, 15),
                    'icone' => 'bx bx-category',
                    'cor' => 'bg-indigo-500',
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'totalPedidos' => $totalPedidos,
            'totalClientes' => $totalClientes,
            'totalProdutos' => $totalProdutos,
            'totalUsuarios' => $totalUsuarios,
            'receitaTotal' => $receitaTotal,
            'pedidosStatusSeries' => $pedidosStatusSeries,
            'vendasMensais' => $vendasMensais,
            'meses' => $meses,
            'produtosNomes' => $produtosNomes,
            'produtosQtd' => $produtosQtd,
            'topUsuarios' => $topUsuarios,
            'vendasRecentes' => $vendasRecentes,
            'produtosRecompra' => $produtosRecompra,
            'topRegioes' => $topRegioes,
            'topCategorias' => $topCategorias,
            'inicio' => $inicio,
            'final' => $final
        ]);
    }
}
