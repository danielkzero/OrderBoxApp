<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Icms_st;
use App\Models\Produtos;
use App\Models\ProdutosConfiguracoesGerais;
use App\Models\ProdutosDestaques;
use App\Models\ProdutosDestaquesItens;
use App\Models\ProdutosImagens;
use App\Models\ProdutosPromocoes;
use App\Models\ProdutosPromocoesItens;
use App\Models\Variacoes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProdutosController extends Controller
{
    private function validarAcessoEmpresa(int|string $empresa): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuario nao logado.');
        }

        $empresaIds = $user->empresas()->pluck('empresas.id')->toArray();
        if (!in_array((int) $empresa, $empresaIds, true)) {
            abort(403, 'Empresa invalida.');
        }
    }

    private function baseUrl(int|string $empresa): string
    {
        return "/{$empresa}/produtos";
    }

    public function index($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $tab = request()->query('tab', 'produtos');
        $subTab = request()->query('sub', 'produtos_tabelas');
        $destaqueId = request()->query('destaque_id');

        $produtos = Produtos::with([
            'imagens',
            'categorias',
            'precos.tabelas',
            'grades.variacoes',
        ])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get();

        $categorias = Categorias::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get();

        $variacoes = Variacoes::query()
            ->with(['variacao_itens' => fn($q) => $q->where('excluido', false)->orderBy('nome')])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $tributacoes = Icms_st::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('codigo_ncm')
            ->orderBy('estado_destino')
            ->limit(500)
            ->get();

        $promocoes = ProdutosPromocoes::query()
            ->withCount(['itens as total_itens' => fn($q) => $q->where('excluido', false)])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                return [
                    ...$item->toArray(),
                    'situacao' => $this->situacaoPeriodo($item->data_inicio, $item->data_fim),
                ];
            })
            ->values();

        $destaques = ProdutosDestaques::query()
            ->withCount(['itens as total_itens' => fn($q) => $q->where('excluido', false)])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                return [
                    ...$item->toArray(),
                    'situacao' => $this->situacaoPeriodo($item->data_inicio, $item->data_fim),
                ];
            })
            ->values();

        $destaqueAtivo = null;
        if ($destaqueId) {
            $destaqueAtivo = ProdutosDestaques::query()
                ->with(['itens' => fn($q) => $q->where('excluido', false)->with('produto.imagens')])
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->find($destaqueId);
        }

        $configuracoesGerais = ProdutosConfiguracoesGerais::query()
            ->firstOrCreate(
                ['empresa_id' => (int)$empresa],
                ['inativos_recentes_dias' => 180, 'inativos_antigos_dias' => 365]
            );

        return Inertia::render('Produtos/Index', [
            'produtos' => $produtos,
            'empresa_selecionada' => (int)$empresa,
            'categorias' => $categorias,
            'variacoes' => $variacoes,
            'tributacoes' => $tributacoes,
            'promocoes' => $promocoes,
            'destaques' => $destaques,
            'destaque_ativo' => $destaqueAtivo,
            'configuracoes_gerais' => $configuracoesGerais,
            'active_tab' => $tab,
            'active_sub_tab' => $subTab,
            'base_url' => $this->baseUrl($empresa),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nome' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo' => 'nullable|string|max:255',
            'preco_tabela' => 'nullable|numeric',
            'preco_minimo' => 'nullable|numeric',
        ]);

        $produto = Produtos::create($request->all());
        return response()->json($produto, 201);
    }

    public function show($id)
    {
        $produto = Produtos::with([
            'categorias',
            'precos',
            'grades.variacoes',
        ])
            ->findOrFail($id);

        return response()->json($produto);
    }

    public function update(Request $request, $id)
    {
        $produto = Produtos::findOrFail($id);
        $produto->fill($request->all());
        $produto->ultima_alteracao = now();
        $produto->save();

        return response()->json($produto);
    }

    public function destroy($id)
    {
        $produto = Produtos::findOrFail($id);
        $produto->delete();

        return response()->json(['message' => 'Produto deletado com sucesso']);
    }

    public function importarFotos(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $importadas = 0;

        foreach ($validated['files'] as $file) {
            $codigo = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $produto = Produtos::query()
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->where('codigo', $codigo)
                ->first();

            if (!$produto) {
                continue;
            }

            $imagem = base64_encode($file->get());
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $base64 = "data:{$mime};base64,{$imagem}";

            ProdutosImagens::create([
                'empresa_id' => (int)$empresa,
                'produto_id' => $produto->id,
                'imagem_base64' => $base64,
                'ordem' => 0,
            ]);

            $importadas++;
        }

        return back()->with('success', "{$importadas} imagem(ns) importada(s) com sucesso.");
    }

    public function excluirTodasImagens($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        ProdutosImagens::query()
            ->where('empresa_id', $empresa)
            ->delete();

        return back()->with('success', 'Todas as imagens foram excluidas.');
    }

    public function storeCategoria(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'categoria_pai_id' => ['nullable', 'integer', 'exists:categorias,id'],
        ]);

        Categorias::create([
            'empresa_id' => (int)$empresa,
            'nome' => $validated['nome'],
            'categoria_pai_id' => $validated['categoria_pai_id'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Categoria criada com sucesso.');
    }

    public function updateCategoria(Request $request, $empresa, $categoria)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
        ]);

        $model = Categorias::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($categoria);

        $model->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroyCategoria($empresa, $categoria)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = Categorias::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($categoria);

        $model->update([
            'excluido' => true,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Categoria removida com sucesso.');
    }

    public function storeVariacao(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
        ]);

        $ordem = (int)Variacoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->max('ordem') + 1;

        Variacoes::create([
            'empresa_id' => (int)$empresa,
            'nome' => $validated['nome'],
            'ordem' => $ordem,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Variacao criada com sucesso.');
    }

    public function updateVariacao(Request $request, $empresa, $variacao)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
        ]);

        $model = Variacoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($variacao);

        $model->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Variacao atualizada com sucesso.');
    }

    public function destroyVariacao($empresa, $variacao)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = Variacoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($variacao);

        $model->update([
            'excluido' => true,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Variacao removida com sucesso.');
    }

    public function salvarInatividade(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'inativos_recentes_dias' => ['required', 'integer', 'min:1', 'max:5000'],
            'inativos_antigos_dias' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        ProdutosConfiguracoesGerais::updateOrCreate(
            ['empresa_id' => (int)$empresa],
            [
                'inativos_recentes_dias' => $validated['inativos_recentes_dias'],
                'inativos_antigos_dias' => $validated['inativos_antigos_dias'],
                'ultima_alteracao' => now(),
            ]
        );

        return back()->with('success', 'Configuracao de inatividade salva com sucesso.');
    }

    public function storeTributacao(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'codigo_ncm' => ['required', 'string', 'max:20'],
            'nome_excecao_fiscal' => ['nullable', 'string', 'max:120'],
            'estado_destino' => ['required', 'string', 'size:2'],
            'tipo_st' => ['nullable', 'string', 'max:10'],
            'valor_mva' => ['nullable', 'numeric'],
            'valor_pmc' => ['nullable', 'numeric'],
            'icms_credito' => ['nullable', 'numeric'],
            'icms_destino' => ['nullable', 'numeric'],
            'preco_considerado_no_calculo' => ['nullable', 'string', 'max:100'],
            'reducao_de_base' => ['nullable', 'numeric'],
        ]);

        Icms_st::create([
            ...$validated,
            'empresa_id' => (int)$empresa,
            'estado_destino' => strtoupper($validated['estado_destino']),
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Regra de tributacao criada com sucesso.');
    }

    public function updateTributacao(Request $request, $empresa, $tributacao)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'codigo_ncm' => ['required', 'string', 'max:20'],
            'nome_excecao_fiscal' => ['nullable', 'string', 'max:120'],
            'estado_destino' => ['required', 'string', 'size:2'],
            'tipo_st' => ['nullable', 'string', 'max:10'],
            'valor_mva' => ['nullable', 'numeric'],
            'valor_pmc' => ['nullable', 'numeric'],
            'icms_credito' => ['nullable', 'numeric'],
            'icms_destino' => ['nullable', 'numeric'],
            'preco_considerado_no_calculo' => ['nullable', 'string', 'max:100'],
            'reducao_de_base' => ['nullable', 'numeric'],
        ]);

        $model = Icms_st::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($tributacao);

        $model->update([
            ...$validated,
            'estado_destino' => strtoupper($validated['estado_destino']),
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Regra de tributacao atualizada com sucesso.');
    }

    public function destroyTributacao($empresa, $tributacao)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = Icms_st::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($tributacao);

        $model->update([
            'excluido' => true,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Regra de tributacao removida com sucesso.');
    }

    public function storePromocao(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'produto_ids' => ['nullable', 'array'],
            'produto_ids.*' => ['integer', 'exists:produtos,id'],
        ]);

        $promocao = ProdutosPromocoes::create([
            'empresa_id' => (int)$empresa,
            'nome' => $validated['nome'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_fim' => $validated['data_fim'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        foreach (($validated['produto_ids'] ?? []) as $produtoId) {
            ProdutosPromocoesItens::firstOrCreate([
                'empresa_id' => (int)$empresa,
                'promocao_id' => $promocao->id,
                'produto_id' => $produtoId,
            ], ['excluido' => false]);
        }

        return back()->with('success', 'Promocao criada com sucesso.');
    }

    public function destroyPromocao($empresa, $promocao)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = ProdutosPromocoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($promocao);

        $model->update(['excluido' => true, 'ultima_alteracao' => now()]);

        return back()->with('success', 'Promocao removida com sucesso.');
    }

    public function storeDestaque(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'produto_ids' => ['nullable', 'array'],
            'produto_ids.*' => ['integer', 'exists:produtos,id'],
        ]);

        $destaque = ProdutosDestaques::create([
            'empresa_id' => (int)$empresa,
            'nome' => $validated['nome'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_fim' => $validated['data_fim'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        foreach (($validated['produto_ids'] ?? []) as $produtoId) {
            ProdutosDestaquesItens::firstOrCreate([
                'empresa_id' => (int)$empresa,
                'destaque_id' => $destaque->id,
                'produto_id' => $produtoId,
            ], ['excluido' => false]);
        }

        return back()->with('success', 'Destaque criado com sucesso.');
    }

    public function updateDestaque(Request $request, $empresa, $destaque)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $model = ProdutosDestaques::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        $model->update([
            'nome' => $validated['nome'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_fim' => $validated['data_fim'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Destaque atualizado com sucesso.');
    }

    public function destroyDestaque($empresa, $destaque)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = ProdutosDestaques::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        $model->update(['excluido' => true, 'ultima_alteracao' => now()]);

        return redirect($this->baseUrl($empresa) . '?tab=destaques')->with('success', 'Destaque removido com sucesso.');
    }

    public function addProdutoDestaque(Request $request, $empresa, $destaque)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'produto_id' => ['required', 'integer', 'exists:produtos,id'],
        ]);

        $destaqueModel = ProdutosDestaques::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        ProdutosDestaquesItens::updateOrCreate(
            [
                'empresa_id' => (int)$empresa,
                'destaque_id' => $destaqueModel->id,
                'produto_id' => $validated['produto_id'],
            ],
            ['excluido' => false]
        );

        return back()->with('success', 'Produto adicionado ao destaque.');
    }

    public function removeProdutoDestaque($empresa, $destaque, $item)
    {
        $this->validarAcessoEmpresa($empresa);

        $destaqueModel = ProdutosDestaques::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        $itemModel = ProdutosDestaquesItens::query()
            ->where('empresa_id', $empresa)
            ->where('destaque_id', $destaqueModel->id)
            ->findOrFail($item);

        $itemModel->update(['excluido' => true]);

        return back()->with('success', 'Produto removido do destaque.');
    }

    private function situacaoPeriodo($inicio, $fim): string
    {
        $hoje = Carbon::today();
        $dataInicio = $inicio ? Carbon::parse($inicio) : null;
        $dataFim = $fim ? Carbon::parse($fim) : null;

        if ($dataFim && $dataFim->lt($hoje)) {
            return 'Finalizada';
        }

        if ($dataInicio && $dataInicio->gt($hoje)) {
            return 'Agendada';
        }

        return 'Ativa';
    }
}
