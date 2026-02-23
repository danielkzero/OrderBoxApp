<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Icms_st;
use App\Models\Produtos;
use App\Models\ProdutosConfiguracoesGerais;
use App\Models\ProdutosDestaques;
use App\Models\ProdutosDestaquesItens;
use App\Models\ProdutosImagens;
use App\Models\ProdutosPrecos;
use App\Models\ProdutosPromocoes;
use App\Models\ProdutosPromocoesItens;
use App\Models\TabelasPrecos;
use App\Models\Variacoes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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

    private function dadosFormularioProduto(int|string $empresa): array
    {
        $categorias = Categorias::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $tabelasPrecos = TabelasPrecos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $variacoes = Variacoes::query()
            ->with(['variacao_itens' => fn($q) => $q->where('excluido', false)->orderBy('nome')])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome', 'ordem']);

        return [
            'categorias' => $categorias,
            'tabelas_precos' => $tabelasPrecos,
            'variacoes' => $variacoes,
        ];
    }

    private function produtoParaFormulario(Produtos $produto): array
    {
        $precosTabelas = $produto
            ->precos
            ->where('excluido', false)
            ->pluck('preco', 'tabela_id')
            ->map(fn($valor) => (float) $valor)
            ->toArray();

        $imagemAtual = $produto
            ->imagens
            ->sortBy('ordem')
            ->first();

        return [
            'id' => $produto->id,
            'codigo' => $produto->codigo,
            'nome' => $produto->nome,
            'unidade' => $produto->unidade,
            'multiplo' => $produto->multiplo,
            'categoria_id' => $produto->categoria_id,
            'moeda' => $produto->moeda ?: 'R$',
            'preco_tabela' => (float) ($produto->preco_tabela ?? 0),
            'preco_minimo' => (float) ($produto->preco_minimo ?? 0),
            'precos_tabelas' => $precosTabelas,
            'ipi' => (float) ($produto->ipi ?? 0),
            'tipo_ipi' => $produto->tipo_ipi ?: '%',
            'comissao' => (float) ($produto->comissao ?? 0),
            'codigo_ncm' => $produto->codigo_ncm,
            'observacoes' => $produto->observacoes,
            'peso_dimensoes_unitario' => (bool) $produto->peso_dimensoes_unitario,
            'peso_bruto' => $produto->peso_bruto,
            'largura' => $produto->largura,
            'altura' => $produto->altura,
            'comprimento' => $produto->comprimento,
            'ativo' => (bool) $produto->ativo,
            'exibir_no_b2b' => (bool) $produto->exibir_no_b2b,
            'imagem_base64' => $imagemAtual?->imagem_base64,
            'imagens' => $produto
                ->imagens
                ->sortBy(fn($img) => sprintf('%010d-%010d', (int) $img->ordem, (int) $img->id))
                ->values()
                ->map(fn($img) => [
                    'id' => $img->id,
                    'imagem_base64' => $img->imagem_base64,
                    'ordem' => $img->ordem,
                    'created_at' => $img->created_at,
                ])
                ->all(),
        ];
    }

    private function regrasValidacaoProduto(int|string $empresa, ?int $produtoId = null): array
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('produtos', 'codigo')
                    ->where(fn($q) => $q
                        ->where('empresa_id', (int) $empresa)
                        ->where('excluido', false))
                    ->ignore($produtoId),
            ],
            'nome' => ['required', 'string', 'max:255'],
            'unidade' => ['nullable', 'string', 'max:10'],
            'multiplo' => ['nullable', 'integer', 'min:1'],
            'moeda' => ['nullable', 'string', 'max:5'],
            'preco_tabela' => ['required', 'numeric', 'min:0'],
            'preco_minimo' => ['nullable', 'numeric', 'min:0'],
            'precos_tabelas' => ['nullable', 'array'],
            'precos_tabelas.*' => ['nullable', 'numeric', 'min:0'],
            'ipi' => ['nullable', 'numeric', 'min:0'],
            'tipo_ipi' => ['nullable', 'string', 'max:5'],
            'comissao' => ['nullable', 'numeric', 'min:0'],
            'codigo_ncm' => ['nullable', 'string', 'max:20'],
            'observacoes' => ['nullable', 'string'],
            'peso_dimensoes_unitario' => ['nullable', 'boolean'],
            'peso_bruto' => ['nullable', 'numeric', 'min:0'],
            'largura' => ['nullable', 'numeric', 'min:0'],
            'altura' => ['nullable', 'numeric', 'min:0'],
            'comprimento' => ['nullable', 'numeric', 'min:0'],
            'exibir_no_b2b' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'imagem' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'imagens' => ['nullable', 'array'],
            'imagens.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ];
    }

    private function syncImagemProduto(int|string $empresa, int $produtoId, ?UploadedFile $imagem): void
    {
        if (!$imagem) {
            return;
        }

        $mime = $imagem->getMimeType() ?: 'image/jpeg';
        $base64 = 'data:' . $mime . ';base64,' . base64_encode($imagem->get());

        ProdutosImagens::query()->updateOrCreate(
            [
                'empresa_id' => (int) $empresa,
                'produto_id' => $produtoId,
                'ordem' => 0,
            ],
            [
                'imagem_base64' => $base64,
            ]
        );
    }

    private function addImagensProduto(int|string $empresa, int $produtoId, array $imagens): void
    {
        if (empty($imagens)) {
            return;
        }

        $ordem = (int) ProdutosImagens::query()
            ->where('empresa_id', $empresa)
            ->where('produto_id', $produtoId)
            ->max('ordem');

        foreach ($imagens as $imagem) {
            $mime = $imagem->getMimeType() ?: 'image/jpeg';
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($imagem->get());
            $ordem++;

            ProdutosImagens::create([
                'empresa_id' => (int) $empresa,
                'produto_id' => $produtoId,
                'imagem_base64' => $base64,
                'ordem' => $ordem,
            ]);
        }
    }

    private function listarImagensProduto(int|string $empresa, int $produtoId): array
    {
        return ProdutosImagens::query()
            ->where('empresa_id', $empresa)
            ->where('produto_id', $produtoId)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get(['id', 'imagem_base64', 'ordem', 'created_at'])
            ->toArray();
    }

    private function mapearPayloadProduto(array $validated): array
    {
        return [
            'codigo' => $validated['codigo'],
            'nome' => $validated['nome'],
            'unidade' => $validated['unidade'] ?? null,
            'multiplo' => $validated['multiplo'] ?? null,
            'moeda' => $validated['moeda'] ?? 'R$',
            'preco_tabela' => $validated['preco_tabela'],
            'preco_minimo' => $validated['preco_minimo'] ?? null,
            'ipi' => $validated['ipi'] ?? null,
            'tipo_ipi' => $validated['tipo_ipi'] ?? null,
            'comissao' => $validated['comissao'] ?? null,
            'codigo_ncm' => $validated['codigo_ncm'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
            'peso_dimensoes_unitario' => (bool) ($validated['peso_dimensoes_unitario'] ?? true),
            'peso_bruto' => $validated['peso_bruto'] ?? null,
            'largura' => $validated['largura'] ?? null,
            'altura' => $validated['altura'] ?? null,
            'comprimento' => $validated['comprimento'] ?? null,
            'exibir_no_b2b' => (bool) ($validated['exibir_no_b2b'] ?? false),
            'ativo' => (bool) ($validated['ativo'] ?? true),
            'categoria_id' => $validated['categoria_id'] ?? null,
            'ultima_alteracao' => now(),
        ];
    }

    private function syncPrecosTabela(int|string $empresa, int $produtoId, array $precosTabelas): void
    {
        $tabelasIds = TabelasPrecos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->pluck('id')
            ->all();

        $ativos = [];

        foreach ($tabelasIds as $tabelaId) {
            $valor = $precosTabelas[$tabelaId] ?? $precosTabelas[(string) $tabelaId] ?? null;

            if ($valor === null || $valor === '') {
                continue;
            }

            $ativo = ProdutosPrecos::query()->updateOrCreate(
                [
                    'empresa_id' => (int) $empresa,
                    'produto_id' => $produtoId,
                    'tabela_id' => (int) $tabelaId,
                ],
                [
                    'preco' => $valor,
                    'excluido' => false,
                    'ultima_alteracao' => now(),
                ]
            );

            $ativos[] = $ativo->id;
        }

        ProdutosPrecos::query()
            ->where('empresa_id', $empresa)
            ->where('produto_id', $produtoId)
            ->when(!empty($ativos), fn($q) => $q->whereNotIn('id', $ativos))
            ->update([
                'excluido' => true,
                'ultima_alteracao' => now(),
            ]);
    }

    public function index($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $tab = request()->query('tab', 'produtos');
        $subTab = request()->query('sub', 'produtos_tabelas');
        $destaqueId = request()->query('destaque_id');

        $produtos = Produtos::with([
            'imagens' => fn($q) => $q->orderBy('ordem'),
            'categorias',
            'precos' => fn($q) => $q
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->with(['tabelas' => fn($t) => $t
                    ->where('empresa_id', $empresa)
                    ->where('excluido', false)]),
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

        $tabelasPrecos = TabelasPrecos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome']);

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
                ['empresa_id' => (int) $empresa],
                ['inativos_recentes_dias' => 180, 'inativos_antigos_dias' => 365]
            );

        return Inertia::render('Produtos/Index', [
            'produtos' => $produtos,
            'empresa_selecionada' => (int) $empresa,
            'categorias' => $categorias,
            'tabelas_precos' => $tabelasPrecos,
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

    public function create($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Produtos/Form', [
            'empresa_id' => (int) $empresa,
            'is_edit' => false,
            'produto' => null,
            ...$this->dadosFormularioProduto($empresa),
        ]);
    }

    public function store(Request $request, $empresa): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate($this->regrasValidacaoProduto($empresa));

        $produto = DB::transaction(function () use ($empresa, $validated, $request) {
            $produtoCriado = Produtos::create([
                'empresa_id' => (int) $empresa,
                ...$this->mapearPayloadProduto($validated),
                'excluido' => false,
            ]);

            $this->syncPrecosTabela($empresa, (int) $produtoCriado->id, $validated['precos_tabelas'] ?? []);
            $this->syncImagemProduto($empresa, (int) $produtoCriado->id, request()->file('imagem'));
            $this->addImagensProduto($empresa, (int) $produtoCriado->id, $request->file('imagens', []));

            return $produtoCriado;
        });

        return redirect("/{$empresa}/produtos/{$produto->id}/edit")
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function show($empresa, $produto): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);
        return redirect("/{$empresa}/produtos/{$produto}/edit");
    }

    public function edit($empresa, $produto): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->with([
                'precos' => fn($q) => $q->where('excluido', false),
                'imagens',
            ])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        return Inertia::render('Produtos/Form', [
            'empresa_id' => (int) $empresa,
            'is_edit' => true,
            'produto' => $this->produtoParaFormulario($produtoModel),
            ...$this->dadosFormularioProduto($empresa),
        ]);
    }

    public function update(Request $request, $empresa, $produto): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        $validated = $request->validate(
            $this->regrasValidacaoProduto($empresa, (int) $produtoModel->id)
        );

        DB::transaction(function () use ($produtoModel, $validated, $empresa, $request) {
            $produtoModel->update($this->mapearPayloadProduto($validated));
            $this->syncPrecosTabela($empresa, (int) $produtoModel->id, $validated['precos_tabelas'] ?? []);
            $this->syncImagemProduto($empresa, (int) $produtoModel->id, request()->file('imagem'));
            $this->addImagensProduto($empresa, (int) $produtoModel->id, $request->file('imagens', []));
        });

        return back()->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy($empresa, $produto): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        $produtoModel->update([
            'excluido' => true,
            'ativo' => false,
            'ultima_alteracao' => now(),
        ]);

        return redirect("/{$empresa}/produtos")
            ->with('success', 'Produto excluido com sucesso.');
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
                'empresa_id' => (int) $empresa,
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

    public function storeImagemProduto(Request $request, $empresa, $produto)
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        $validated = $request->validate([
            'imagens' => ['required', 'array', 'min:1'],
            'imagens.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $this->addImagensProduto($empresa, (int) $produtoModel->id, $validated['imagens']);

        return response()->json([
            'success' => true,
            'imagens' => $this->listarImagensProduto($empresa, (int) $produtoModel->id),
        ]);
    }

    public function updateOrdenacaoImagensProduto(Request $request, $empresa, $produto)
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        $validated = $request->validate([
            'imagens' => ['required', 'array', 'min:1'],
            'imagens.*.id' => ['required', 'integer'],
            'imagens.*.ordem' => ['required', 'integer', 'min:0'],
        ]);

        $ids = collect($validated['imagens'])->pluck('id')->map(fn($id) => (int) $id)->all();
        $existentes = ProdutosImagens::query()
            ->where('empresa_id', $empresa)
            ->where('produto_id', $produtoModel->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (count($existentes) !== count($ids)) {
            abort(422, 'Uma ou mais imagens informadas nao pertencem ao produto.');
        }

        DB::transaction(function () use ($validated, $empresa, $produtoModel) {
            foreach ($validated['imagens'] as $img) {
                ProdutosImagens::query()
                    ->where('empresa_id', $empresa)
                    ->where('produto_id', $produtoModel->id)
                    ->where('id', (int) $img['id'])
                    ->update(['ordem' => (int) $img['ordem']]);
            }
        });

        return response()->json([
            'success' => true,
            'imagens' => $this->listarImagensProduto($empresa, (int) $produtoModel->id),
        ]);
    }

    public function destroyImagemProduto($empresa, $produto, $imagem)
    {
        $this->validarAcessoEmpresa($empresa);

        $produtoModel = Produtos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($produto);

        $imagemModel = ProdutosImagens::query()
            ->where('empresa_id', $empresa)
            ->where('produto_id', $produtoModel->id)
            ->findOrFail($imagem);

        $imagemModel->delete();

        return response()->json([
            'success' => true,
            'imagens' => $this->listarImagensProduto($empresa, (int) $produtoModel->id),
        ]);
    }

    public function updateTabelaPreco(Request $request, $empresa, $tabela): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $tabelaModel = TabelasPrecos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($tabela);

        $tabelaModel->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Nome da tabela de preco atualizado com sucesso.');
    }

    public function storeCategoria(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'categoria_pai_id' => ['nullable', 'integer', 'exists:categorias,id'],
        ]);

        Categorias::create([
            'empresa_id' => (int) $empresa,
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

        $ordem = (int) Variacoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->max('ordem') + 1;

        Variacoes::create([
            'empresa_id' => (int) $empresa,
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
            ['empresa_id' => (int) $empresa],
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
            'empresa_id' => (int) $empresa,
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
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_fim' => $validated['data_fim'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        foreach (($validated['produto_ids'] ?? []) as $produtoId) {
            ProdutosPromocoesItens::firstOrCreate([
                'empresa_id' => (int) $empresa,
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
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'data_inicio' => $validated['data_inicio'] ?? null,
            'data_fim' => $validated['data_fim'] ?? null,
            'ultima_alteracao' => now(),
        ]);

        foreach (($validated['produto_ids'] ?? []) as $produtoId) {
            ProdutosDestaquesItens::firstOrCreate([
                'empresa_id' => (int) $empresa,
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
                'empresa_id' => (int) $empresa,
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
