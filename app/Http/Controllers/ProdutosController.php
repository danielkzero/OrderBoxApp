<?php

namespace App\Http\Controllers;

use App\Models\Categorias;
use App\Models\Icms_st;
use App\Models\Produtos;
use App\Models\ProdutosConfiguracoesGerais;
use App\Models\ProdutosDestaques;
use App\Models\ProdutosDestaquesItens;
use App\Models\ProdutosEstoqueMovimentos;
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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

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
            'imagem' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'imagens' => ['nullable', 'array'],
            'imagens.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
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

        $tab = request()->route('tab') ?? request()->query('tab', 'produtos');
        $subTab = request()->route('sub') ?? request()->query('sub', 'produtos_tabelas');

        $tabsValidas = ['produtos', 'promocoes', 'destaques', 'configuracoes'];
        $subsValidas = ['produtos_tabelas', 'gerenciar_estoque', 'importar_fotos', 'categorias', 'variacoes', 'inatividade', 'tributacoes'];

        if (!in_array($tab, $tabsValidas, true)) {
            $tab = 'produtos';
        }

        if (!in_array($subTab, $subsValidas, true)) {
            $subTab = $tab === 'configuracoes' ? 'categorias' : 'produtos_tabelas';
        }
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
                ['inativos_recentes_dias' => 180, 'inativos_antigos_dias' => 365, 'gerenciar_estoque' => false]
            );

        $limiteHistoricoGeral = 300;

        $movimentosEstoque = ProdutosEstoqueMovimentos::query()
            ->with([
                'produto:id,empresa_id,codigo,nome',
                'user:id,name',
            ])
            ->where('empresa_id', $empresa)
            ->orderByDesc('id')
            ->limit($limiteHistoricoGeral)
            ->get()
            ->map(fn($movimento) => [
                'id' => $movimento->id,
                'produto_id' => $movimento->produto_id,
                'produto_nome' => $movimento->produto?->nome,
                'produto_codigo' => $movimento->produto?->codigo,
                'tipo' => $movimento->tipo,
                'quantidade' => (float) $movimento->quantidade,
                'saldo_anterior' => (float) ($movimento->saldo_anterior ?? 0),
                'saldo_atual' => (float) ($movimento->saldo_atual ?? 0),
                'observacoes' => $movimento->observacoes,
                'origem' => $movimento->origem,
                'usuario_nome' => $movimento->user?->name,
                'created_at' => $movimento->created_at,
            ])
            ->values();

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
            'movimentos_estoque_geral' => $movimentosEstoque,
            'estoque_limites' => [
                'historico_geral' => $limiteHistoricoGeral,
            ],
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

    public function createImportacao($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Produtos/Importacao/Index', [
            'empresa_id' => (int) $empresa,
        ]);
    }

    private function colToNumber(string $col): int
    {
        $number = 0;
        foreach (str_split(strtoupper($col)) as $char) {
            if ($char < 'A' || $char > 'Z') {
                continue;
            }
            $number = ($number * 26) + (ord($char) - 64);
        }

        return $number;
    }

    private function normalizarTextoCell(?string $valor): string
    {
        if ($valor === null) {
            return '';
        }

        $valor = preg_replace('/\s+/u', ' ', str_replace(["\r", "\n", "\t"], ' ', $valor)) ?? '';
        return trim($valor);
    }

    private function parseXlsxRows(string $arquivoPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($arquivoPath) !== true) {
            throw ValidationException::withMessages([
                'arquivo' => 'Nao foi possivel abrir a planilha.',
            ]);
        }

        try {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $workbookRelsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if ($workbookXml === false || $workbookRelsXml === false) {
                throw ValidationException::withMessages([
                    'arquivo' => 'Planilha invalida: estrutura do workbook nao encontrada.',
                ]);
            }

            $workbook = new \SimpleXMLElement($workbookXml);
            $sheets = $workbook->xpath('//*[local-name()="sheets"]/*[local-name()="sheet"]');

            if (!$sheets || !isset($sheets[0])) {
                throw ValidationException::withMessages([
                    'arquivo' => 'Planilha invalida: nenhuma aba encontrada.',
                ]);
            }

            $firstSheet = $sheets[0];
            $rid = (string) $firstSheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

            $rels = new \SimpleXMLElement($workbookRelsXml);
            $targets = $rels->xpath('//*[local-name()="Relationship"][@Id="' . $rid . '"]');

            if (!$targets || !isset($targets[0])) {
                throw ValidationException::withMessages([
                    'arquivo' => 'Planilha invalida: relacao da aba nao encontrada.',
                ]);
            }

            $sheetTarget = ltrim((string) $targets[0]['Target'], '/');
            if (!Str::startsWith($sheetTarget, 'xl/')) {
                $sheetTarget = 'xl/' . $sheetTarget;
            }

            $sheetXml = $zip->getFromName($sheetTarget);
            if ($sheetXml === false) {
                throw ValidationException::withMessages([
                    'arquivo' => 'Planilha invalida: dados da aba nao encontrados.',
                ]);
            }

            $sharedStrings = [];
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml !== false) {
                $sst = new \SimpleXMLElement($sharedStringsXml);
                $stringItems = $sst->xpath('//*[local-name()="si"]');
                foreach ($stringItems ?: [] as $item) {
                    $texts = $item->xpath('.//*[local-name()="t"]');
                    $sharedStrings[] = $this->normalizarTextoCell(implode('', array_map(fn($textNode) => (string) $textNode, $texts ?: [])));
                }
            }

            $sheet = new \SimpleXMLElement($sheetXml);
            $rows = $sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [];

            $parsed = [];
            foreach ($rows as $rowNode) {
                $rowIndex = (int) ($rowNode['r'] ?? 0);
                $cells = $rowNode->xpath('./*[local-name()="c"]') ?: [];
                $rowValues = [];

                foreach ($cells as $cellNode) {
                    $cellRef = (string) ($cellNode['r'] ?? '');
                    if (!preg_match('/([A-Z]+)/', $cellRef, $match)) {
                        continue;
                    }

                    $colIndex = $this->colToNumber($match[1]);
                    $cellType = (string) ($cellNode['t'] ?? '');
                    $value = '';

                    if ($cellType === 's') {
                        $idxNodes = $cellNode->xpath('./*[local-name()="v"]');
                        $idx = isset($idxNodes[0]) ? (int) ((string) $idxNodes[0]) : null;
                        $value = $idx !== null && isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
                    } elseif ($cellType === 'inlineStr') {
                        $texts = $cellNode->xpath('.//*[local-name()="is"]//*[local-name()="t"]');
                        $value = implode('', array_map(fn($textNode) => (string) $textNode, $texts ?: []));
                    } else {
                        $valNodes = $cellNode->xpath('./*[local-name()="v"]');
                        $value = isset($valNodes[0]) ? (string) $valNodes[0] : '';
                    }

                    $rowValues[$colIndex] = $this->normalizarTextoCell($value);
                }

                $parsed[] = [
                    'row' => $rowIndex,
                    'values' => $rowValues,
                ];
            }

            return $parsed;
        } finally {
            $zip->close();
        }
    }

    private function parseNumeroPlanilha(mixed $valor): ?float
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        $texto = str_replace(['R$', '%', ' '], '', $texto);

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            if (strrpos($texto, ',') > strrpos($texto, '.')) {
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                $texto = str_replace(',', '', $texto);
            }
        } elseif (str_contains($texto, ',')) {
            $texto = str_replace(',', '.', $texto);
        }

        return is_numeric($texto) ? (float) $texto : null;
    }

    private function parseInteiroPlanilha(mixed $valor): ?int
    {
        $numero = $this->parseNumeroPlanilha($valor);
        return $numero === null ? null : (int) round($numero);
    }

    private function obterOuCriarCategoriaImportacao(int|string $empresa, string $nome, ?int $categoriaPaiId, array &$cache): ?Categorias
    {
        $nome = trim($nome);
        if ($nome === '') {
            return null;
        }

        $cacheKey = mb_strtolower($nome) . '|' . ($categoriaPaiId ?? 0);
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $categoria = Categorias::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->where('categoria_pai_id', $categoriaPaiId)
            ->first();

        if (!$categoria) {
            $categoria = Categorias::create([
                'empresa_id' => (int) $empresa,
                'nome' => $nome,
                'categoria_pai_id' => $categoriaPaiId,
                'ultima_alteracao' => now(),
                'excluido' => false,
            ]);
        }

        $cache[$cacheKey] = $categoria;
        return $categoria;
    }

    public function importarProdutosPlanilha(Request $request, $empresa): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'modo' => ['required', Rule::in(['atualizar', 'substituir'])],
            'arquivo' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        $rows = $this->parseXlsxRows($request->file('arquivo')->getRealPath());
        if (empty($rows)) {
            return back()->withErrors(['arquivo' => 'A planilha esta vazia.'])->withInput();
        }

        $dadosRows = array_values(array_filter($rows, function (array $row) {
            return (int) ($row['row'] ?? 0) > 1;
        }));

        if (empty($dadosRows)) {
            return back()->withErrors(['arquivo' => 'A planilha nao possui linhas de produtos para importar.'])->withInput();
        }

        $tabelasPrecos = TabelasPrecos::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->orderBy('id')
            ->get(['id'])
            ->values();

        $criados = 0;
        $atualizados = 0;
        $ignorados = 0;
        $erros = [];
        $categoriaCache = [];

        DB::transaction(function () use (
            $empresa,
            $validated,
            $dadosRows,
            $tabelasPrecos,
            &$criados,
            &$atualizados,
            &$ignorados,
            &$erros,
            &$categoriaCache
        ) {
            if ($validated['modo'] === 'substituir') {
                Produtos::query()
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)
                    ->update([
                        'excluido' => true,
                        'ativo' => false,
                        'ultima_alteracao' => now(),
                    ]);
            }

            foreach ($dadosRows as $rowData) {
                $rowNum = (int) ($rowData['row'] ?? 0);
                $values = $rowData['values'] ?? [];

                $codigo = trim((string) ($values[1] ?? ''));
                $nome = trim((string) ($values[2] ?? ''));
                $precoTabela = $this->parseNumeroPlanilha($values[3] ?? null);
                $precoMinimo = $this->parseNumeroPlanilha($values[4] ?? null);
                $ipi = $this->parseNumeroPlanilha($values[5] ?? null);
                $codigoNcm = trim((string) ($values[6] ?? ''));
                $comissao = $this->parseNumeroPlanilha($values[7] ?? null);
                $observacoes = trim((string) ($values[8] ?? ''));
                $unidade = trim((string) ($values[9] ?? ''));
                $saldoEstoque = $this->parseNumeroPlanilha($values[10] ?? null);
                $multiplo = $this->parseInteiroPlanilha($values[11] ?? null);
                $pesoBruto = $this->parseNumeroPlanilha($values[12] ?? null);
                $tipoPesoDimensoes = $this->parseInteiroPlanilha($values[13] ?? null);
                $largura = $this->parseNumeroPlanilha($values[14] ?? null);
                $altura = $this->parseNumeroPlanilha($values[15] ?? null);
                $comprimento = $this->parseNumeroPlanilha($values[16] ?? null);
                $cat1 = trim((string) ($values[17] ?? ''));
                $cat2 = trim((string) ($values[18] ?? ''));
                $cat3 = trim((string) ($values[19] ?? ''));
                $flagInativo = $this->parseInteiroPlanilha($values[20] ?? null);
                $flagNaoExibir = $this->parseInteiroPlanilha($values[21] ?? null);

                $temConteudo = collect($values)->filter(fn($value) => trim((string) $value) !== '')->isNotEmpty();
                if (!$temConteudo) {
                    continue;
                }

                if ($nome === '' || $precoTabela === null) {
                    $ignorados++;
                    $erros[] = "Linha {$rowNum}: nome e preco de tabela sao obrigatorios.";
                    continue;
                }

                $produto = null;
                if ($codigo !== '') {
                    $produto = Produtos::query()
                        ->where('empresa_id', (int) $empresa)
                        ->where('codigo', $codigo)
                        ->first();
                }

                if (!$produto) {
                    $produto = Produtos::query()
                        ->where('empresa_id', (int) $empresa)
                        ->where('excluido', false)
                        ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
                        ->first();
                }

                $categoriaNivel1 = $this->obterOuCriarCategoriaImportacao($empresa, $cat1, null, $categoriaCache);
                $categoriaNivel2 = $this->obterOuCriarCategoriaImportacao($empresa, $cat2, $categoriaNivel1?->id, $categoriaCache);
                $categoriaNivel3 = $this->obterOuCriarCategoriaImportacao($empresa, $cat3, $categoriaNivel2?->id, $categoriaCache);
                $categoriaFinal = $categoriaNivel3 ?? $categoriaNivel2 ?? $categoriaNivel1;

                $ativo = $produto?->ativo ?? true;
                if ($flagInativo !== null) {
                    $ativo = $flagInativo === 0;
                }

                $exibirNoB2b = $produto?->exibir_no_b2b ?? true;
                if ($flagNaoExibir !== null) {
                    $exibirNoB2b = $flagNaoExibir === 0;
                }

                $payload = [
                    'categoria_id' => $categoriaFinal?->id,
                    'codigo' => $codigo !== '' ? $codigo : ($produto?->codigo ?: ('IMP-' . now()->format('YmdHis') . '-' . $rowNum)),
                    'nome' => $nome,
                    'preco_tabela' => $precoTabela,
                    'preco_minimo' => $precoMinimo,
                    'ipi' => $ipi,
                    'tipo_ipi' => '%',
                    'comissao' => $comissao,
                    'codigo_ncm' => $codigoNcm !== '' ? $codigoNcm : null,
                    'observacoes' => $observacoes !== '' ? $observacoes : null,
                    'unidade' => $unidade !== '' ? $unidade : null,
                    'saldo_estoque' => $saldoEstoque !== null ? max(0, $saldoEstoque) : ($produto?->saldo_estoque ?? 0),
                    'multiplo' => $multiplo !== null && $multiplo > 0 ? $multiplo : ($produto?->multiplo ?: 1),
                    'peso_bruto' => $pesoBruto,
                    'largura' => $largura,
                    'altura' => $altura,
                    'comprimento' => $comprimento,
                    'peso_dimensoes_unitario' => $tipoPesoDimensoes === 1 ? false : true,
                    'ativo' => $ativo,
                    'exibir_no_b2b' => $exibirNoB2b,
                    'moeda' => $produto?->moeda ?: 'R$',
                    'excluido' => false,
                    'ultima_alteracao' => now(),
                ];

                if ($produto) {
                    $produto->update($payload);
                    $atualizados++;
                } else {
                    $produto = Produtos::create([
                        'empresa_id' => (int) $empresa,
                        ...$payload,
                    ]);
                    $criados++;
                }

                $precosTabelas = [];
                foreach ($tabelasPrecos as $index => $tabela) {
                    $excelCol = 22 + $index;
                    if ($excelCol > 40) {
                        break;
                    }
                    $valorTabela = $this->parseNumeroPlanilha($values[$excelCol] ?? null);
                    if ($valorTabela !== null) {
                        $precosTabelas[(int) $tabela->id] = $valorTabela;
                    }
                }
                $this->syncPrecosTabela($empresa, (int) $produto->id, $precosTabelas);
            }
        });

        $mensagem = "Importacao finalizada: {$criados} criado(s), {$atualizados} atualizado(s), {$ignorados} ignorado(s).";

        if (!empty($erros)) {
            return back()
                ->with('warning', $mensagem)
                ->with('import_errors', array_slice($erros, 0, 20));
        }

        return back()->with('success', $mensagem);
    }

    public function importarFotos(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
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
            'imagens.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
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

    public function salvarGerenciarEstoque(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'gerenciar_estoque' => ['required', 'boolean'],
        ]);

        $gerenciarEstoque = (bool) $validated['gerenciar_estoque'];

        ProdutosConfiguracoesGerais::updateOrCreate(
            ['empresa_id' => (int) $empresa],
            [
                'gerenciar_estoque' => $gerenciarEstoque,
                'ultima_alteracao' => now(),
            ]
        );

        if ($gerenciarEstoque) {
            Produtos::query()
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->where(fn($q) => $q->whereNull('saldo_estoque')->orWhere('saldo_estoque', '<=', 0))
                ->update([
                    'ativo' => false,
                    'ultima_alteracao' => now(),
                ]);
        }

        return back()->with('success', $gerenciarEstoque
            ? 'Controle de estoque habilitado com sucesso.'
            : 'Controle de estoque desabilitado com sucesso.');
    }

    public function salvarMovimentoEstoque(Request $request, $empresa): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'produto_id' => [
                'required',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'tipo' => ['required', Rule::in(['entrada', 'saida'])],
            'quantidade' => ['required', 'numeric', 'gt:0'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $empresa) {
            $produto = Produtos::query()
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->lockForUpdate()
                ->findOrFail((int) $validated['produto_id']);

            $saldoAnterior = (float) ($produto->saldo_estoque ?? 0);
            $quantidade = (float) $validated['quantidade'];
            $tipo = $validated['tipo'];

            if ($tipo === 'saida' && $saldoAnterior < $quantidade) {
                throw ValidationException::withMessages([
                    'quantidade' => sprintf(
                        'Estoque insuficiente. Saldo disponivel para %s: %s.',
                        $produto->nome,
                        number_format($saldoAnterior, 2, ',', '.')
                    ),
                ]);
            }

            $saldoAtual = $tipo === 'entrada'
                ? $saldoAnterior + $quantidade
                : $saldoAnterior - $quantidade;

            $saldoAtual = max(0, $saldoAtual);
            $gerenciarEstoqueAtivo = (bool) ProdutosConfiguracoesGerais::query()
                ->where('empresa_id', (int) $empresa)
                ->value('gerenciar_estoque');

            $produto->saldo_estoque = $saldoAtual;
            $produto->ultima_alteracao = now();

            if ($gerenciarEstoqueAtivo) {
                $produto->ativo = $saldoAtual > 0;
            }

            $produto->save();

            ProdutosEstoqueMovimentos::create([
                'empresa_id' => (int) $empresa,
                'produto_id' => (int) $produto->id,
                'user_id' => Auth::id(),
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_atual' => $saldoAtual,
                'observacoes' => $validated['observacoes'] ?? null,
                'origem' => 'manual',
            ]);
        });

        return back()->with('success', 'Movimentacao de estoque registrada com sucesso.');
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
            'produto_ids.*' => [
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'itens' => ['nullable', 'array'],
            'itens.*.produto_id' => [
                'required_with:itens',
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'itens.*.desconto_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($empresa, $validated) {
            $promocao = ProdutosPromocoes::create([
                'empresa_id' => (int) $empresa,
                'nome' => $validated['nome'],
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
                'ultima_alteracao' => now(),
            ]);

            $itens = collect($validated['itens'] ?? [])
                ->map(fn($item) => [
                    'produto_id' => (int) $item['produto_id'],
                    'desconto_percentual' => array_key_exists('desconto_percentual', $item) && $item['desconto_percentual'] !== null
                        ? (float) $item['desconto_percentual']
                        : null,
                ])
                ->values();

            if ($itens->isEmpty()) {
                $itens = collect($validated['produto_ids'] ?? [])
                    ->map(fn($produtoId) => [
                        'produto_id' => (int) $produtoId,
                        'desconto_percentual' => null,
                    ])
                    ->values();
            }

            foreach ($itens as $item) {
                ProdutosPromocoesItens::updateOrCreate(
                    [
                        'empresa_id' => (int) $empresa,
                        'promocao_id' => (int) $promocao->id,
                        'produto_id' => (int) $item['produto_id'],
                    ],
                    [
                        'desconto_percentual' => $item['desconto_percentual'],
                        'excluido' => false,
                    ]
                );
            }
        });

        return redirect("/{$empresa}/produtos/promocoes")->with('success', 'Promocao criada com sucesso.');
    }

    private function carregarDadosFormularioPromocao(int|string $empresa, ?ProdutosPromocoes $promocao = null): array
    {
        $produtos = Produtos::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'codigo', 'nome'])
            ->map(fn($produto) => [
                'id' => $produto->id,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome,
            ])
            ->values();

        $itensSelecionados = collect();
        if ($promocao) {
            $itensSelecionados = ProdutosPromocoesItens::query()
                ->with('produto:id,codigo,nome')
                ->where('empresa_id', (int) $empresa)
                ->where('promocao_id', (int) $promocao->id)
                ->where('excluido', false)
                ->orderBy('id')
                ->get()
                ->map(fn($item) => [
                    'produto_id' => (int) $item->produto_id,
                    'codigo' => $item->produto?->codigo,
                    'nome' => $item->produto?->nome,
                    'desconto_percentual' => $item->desconto_percentual !== null ? (float) $item->desconto_percentual : null,
                ])
                ->values();
        }

        return [
            'empresa_id' => (int) $empresa,
            'promocao' => $promocao ? [
                'id' => (int) $promocao->id,
                'nome' => $promocao->nome,
                'data_inicio' => optional($promocao->data_inicio)->format('Y-m-d'),
                'data_fim' => optional($promocao->data_fim)->format('Y-m-d'),
                'ativo' => (bool) $promocao->ativo,
                'itens' => $itensSelecionados,
            ] : null,
            'produtos' => $produtos,
        ];
    }

    public function createPromocao($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Produtos/Promocoes/Form', [
            ...$this->carregarDadosFormularioPromocao($empresa),
            'is_edit' => false,
        ]);
    }

    public function editPromocao($empresa, $promocao): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $promocaoModel = ProdutosPromocoes::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->findOrFail($promocao);

        return Inertia::render('Produtos/Promocoes/Form', [
            ...$this->carregarDadosFormularioPromocao($empresa, $promocaoModel),
            'is_edit' => true,
        ]);
    }

    public function updatePromocao(Request $request, $empresa, $promocao): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'itens' => ['nullable', 'array'],
            'itens.*.produto_id' => [
                'required_with:itens',
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'itens.*.desconto_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $promocaoModel = ProdutosPromocoes::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->findOrFail($promocao);

        DB::transaction(function () use ($validated, $empresa, $promocaoModel) {
            $promocaoModel->update([
                'nome' => $validated['nome'],
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
                'ativo' => (bool) ($validated['ativo'] ?? true),
                'ultima_alteracao' => now(),
            ]);

            $itens = collect($validated['itens'] ?? [])
                ->map(fn($item) => [
                    'produto_id' => (int) $item['produto_id'],
                    'desconto_percentual' => array_key_exists('desconto_percentual', $item) && $item['desconto_percentual'] !== null
                        ? (float) $item['desconto_percentual']
                        : null,
                ])
                ->unique('produto_id')
                ->values();

            $produtoIdsAtivos = $itens->pluck('produto_id')->all();

            ProdutosPromocoesItens::query()
                ->where('empresa_id', (int) $empresa)
                ->where('promocao_id', (int) $promocaoModel->id)
                ->when(!empty($produtoIdsAtivos), fn($q) => $q->whereNotIn('produto_id', $produtoIdsAtivos))
                ->update(['excluido' => true]);

            foreach ($itens as $item) {
                ProdutosPromocoesItens::updateOrCreate(
                    [
                        'empresa_id' => (int) $empresa,
                        'promocao_id' => (int) $promocaoModel->id,
                        'produto_id' => (int) $item['produto_id'],
                    ],
                    [
                        'desconto_percentual' => $item['desconto_percentual'],
                        'excluido' => false,
                    ]
                );
            }
        });

        return redirect("/{$empresa}/produtos/promocoes")->with('success', 'Promocao atualizada com sucesso.');
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
            'produto_ids.*' => [
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'itens' => ['nullable', 'array'],
            'itens.*.produto_id' => [
                'required_with:itens',
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
        ]);

        DB::transaction(function () use ($empresa, $validated) {
            $destaque = ProdutosDestaques::create([
                'empresa_id' => (int) $empresa,
                'nome' => $validated['nome'],
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
                'ultima_alteracao' => now(),
            ]);

            $itens = collect($validated['itens'] ?? [])
                ->map(fn($item) => (int) $item['produto_id'])
                ->values();

            if ($itens->isEmpty()) {
                $itens = collect($validated['produto_ids'] ?? [])->map(fn($id) => (int) $id)->values();
            }

            foreach ($itens as $produtoId) {
                ProdutosDestaquesItens::updateOrCreate(
                    [
                        'empresa_id' => (int) $empresa,
                        'destaque_id' => (int) $destaque->id,
                        'produto_id' => (int) $produtoId,
                    ],
                    ['excluido' => false]
                );
            }
        });

        return redirect("/{$empresa}/produtos/destaques")->with('success', 'Destaque criado com sucesso.');
    }

    private function carregarDadosFormularioDestaque(int|string $empresa, ?ProdutosDestaques $destaque = null): array
    {
        $produtos = Produtos::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'codigo', 'nome'])
            ->map(fn($produto) => [
                'id' => (int) $produto->id,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome,
            ])
            ->values();

        $itensSelecionados = collect();
        if ($destaque) {
            $itensSelecionados = ProdutosDestaquesItens::query()
                ->with('produto:id,codigo,nome')
                ->where('empresa_id', (int) $empresa)
                ->where('destaque_id', (int) $destaque->id)
                ->where('excluido', false)
                ->orderBy('id')
                ->get()
                ->map(fn($item) => [
                    'produto_id' => (int) $item->produto_id,
                    'codigo' => $item->produto?->codigo,
                    'nome' => $item->produto?->nome,
                ])
                ->values();
        }

        return [
            'empresa_id' => (int) $empresa,
            'destaque' => $destaque ? [
                'id' => (int) $destaque->id,
                'nome' => $destaque->nome,
                'data_inicio' => optional($destaque->data_inicio)->format('Y-m-d'),
                'data_fim' => optional($destaque->data_fim)->format('Y-m-d'),
                'ativo' => (bool) $destaque->ativo,
                'itens' => $itensSelecionados,
            ] : null,
            'produtos' => $produtos,
        ];
    }

    public function createDestaque($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Produtos/Destaques/Form', [
            ...$this->carregarDadosFormularioDestaque($empresa),
            'is_edit' => false,
        ]);
    }

    public function editDestaque($empresa, $destaque): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $destaqueModel = ProdutosDestaques::query()
            ->where('empresa_id', (int) $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        return Inertia::render('Produtos/Destaques/Form', [
            ...$this->carregarDadosFormularioDestaque($empresa, $destaqueModel),
            'is_edit' => true,
        ]);
    }

    public function updateDestaque(Request $request, $empresa, $destaque)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'itens' => ['nullable', 'array'],
            'itens.*.produto_id' => [
                'required_with:itens',
                'integer',
                Rule::exists('produtos', 'id')->where(fn($q) => $q
                    ->where('empresa_id', (int) $empresa)
                    ->where('excluido', false)),
            ],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $model = ProdutosDestaques::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($destaque);

        DB::transaction(function () use ($validated, $empresa, $model) {
            $model->update([
                'nome' => $validated['nome'],
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
                'ativo' => (bool) ($validated['ativo'] ?? true),
                'ultima_alteracao' => now(),
            ]);

            if (!array_key_exists('itens', $validated)) {
                return;
            }

            $produtoIdsAtivos = collect($validated['itens'] ?? [])
                ->map(fn($item) => (int) $item['produto_id'])
                ->unique()
                ->values()
                ->all();

            ProdutosDestaquesItens::query()
                ->where('empresa_id', (int) $empresa)
                ->where('destaque_id', (int) $model->id)
                ->when(!empty($produtoIdsAtivos), fn($q) => $q->whereNotIn('produto_id', $produtoIdsAtivos))
                ->update(['excluido' => true]);

            foreach ($produtoIdsAtivos as $produtoId) {
                ProdutosDestaquesItens::updateOrCreate(
                    [
                        'empresa_id' => (int) $empresa,
                        'destaque_id' => (int) $model->id,
                        'produto_id' => (int) $produtoId,
                    ],
                    ['excluido' => false]
                );
            }
        });

        return redirect("/{$empresa}/produtos/destaques")->with('success', 'Destaque atualizado com sucesso.');
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
