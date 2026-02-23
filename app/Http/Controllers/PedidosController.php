<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\CondicoesPagamentos;
use App\Models\FormasPagamentos;
use App\Models\Pedidos;
use App\Models\PedidosExportacaoConfiguracoes;
use App\Models\PedidosItens;
use App\Models\Produtos;
use App\Models\TabelasPrecosCidades;
use App\Models\TiposPedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PedidosController extends Controller
{
    private const EXPORT_COLUMN_OPTIONS = [
        'ordem',
        'codigo',
        'descricao',
        'quantidade',
        'unidade',
        'desconto',
        'acrescimo',
        'preco_tabela',
        'preco_liquido',
        'st',
        'subtotal',
        'observacoes',
    ];

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

    private function defaultExportConfig(): array
    {
        return [
            'formato' => 'csv',
            'incluir_cabecalho_colunas' => true,
            'incluir_dados_cabecalho' => true,
            'incluir_itens' => true,
            'incluir_informacoes_extras' => true,
            'colunas' => ['ordem', 'codigo', 'descricao', 'quantidade', 'unidade', 'preco_liquido', 'st', 'subtotal'],
        ];
    }

    private function sanitizeExportConfig(?array $config): array
    {
        $base = $this->defaultExportConfig();
        if (!$config) {
            return $base;
        }

        $colunas = collect($config['colunas'] ?? [])
            ->filter(fn ($coluna) => in_array($coluna, self::EXPORT_COLUMN_OPTIONS, true))
            ->values()
            ->all();

        return [
            'formato' => in_array(($config['formato'] ?? ''), ['xls', 'csv', 'txt'], true) ? $config['formato'] : $base['formato'],
            'incluir_cabecalho_colunas' => (bool) ($config['incluir_cabecalho_colunas'] ?? $base['incluir_cabecalho_colunas']),
            'incluir_dados_cabecalho' => (bool) ($config['incluir_dados_cabecalho'] ?? $base['incluir_dados_cabecalho']),
            'incluir_itens' => (bool) ($config['incluir_itens'] ?? $base['incluir_itens']),
            'incluir_informacoes_extras' => (bool) ($config['incluir_informacoes_extras'] ?? $base['incluir_informacoes_extras']),
            'colunas' => $colunas ?: $base['colunas'],
        ];
    }

    private function obterExportConfig(int|string $empresa): array
    {
        $config = PedidosExportacaoConfiguracoes::query()
            ->where('empresa_id', (int) $empresa)
            ->where('user_id', (int) Auth::id())
            ->value('configuracoes');

        return $this->sanitizeExportConfig($config);
    }

    private function carregarDadosFormulario(int|string $empresa): array
    {
        $tabelaPorMunicipio = TabelasPrecosCidades::query()
            ->select([
                'tabelas_precos_cidades.municipio_codigo',
                'tabelas_precos.nome as tabela_preco_nome',
            ])
            ->join('tabelas_precos', 'tabelas_precos.id', '=', 'tabelas_precos_cidades.tabela_preco_id')
            ->where('tabelas_precos_cidades.empresa_id', $empresa)
            ->where('tabelas_precos_cidades.excluido', false)
            ->where('tabelas_precos.excluido', false)
            ->orderBy('tabelas_precos_cidades.id')
            ->get()
            ->keyBy('municipio_codigo');

        $clientes = Clientes::with(['telefones', 'emails', 'contatos.telefones', 'contatos.emails'])
            ->where('empresa_id', $empresa)
            ->orderBy('razao_social')
            ->get()
            ->map(function ($cliente) use ($tabelaPorMunicipio) {
                $tabelaCliente = $tabelaPorMunicipio->get($cliente->municipio_codigo);
                $telefones = $cliente->telefones
                    ->pluck('numero')
                    ->filter()
                    ->values();
                $emails = $cliente->emails
                    ->pluck('email')
                    ->filter()
                    ->values();

                $contatos = $cliente->contatos->map(function ($contato) {
                    return [
                        'id' => $contato->id,
                        'nome' => $contato->nome,
                        'cargo' => $contato->cargo,
                        'telefones' => $contato->telefones->pluck('numero')->filter()->values()->all(),
                        'emails' => $contato->emails->pluck('email')->filter()->values()->all(),
                    ];
                })->values();

                $telefonePrincipal = $telefones->first()
                    ?? $contatos->flatMap(fn ($contato) => $contato['telefones'] ?? [])->first();
                $emailPrincipal = $emails->first()
                    ?? $contatos->flatMap(fn ($contato) => $contato['emails'] ?? [])->first();

                return [
                    'id' => $cliente->id,
                    'razao_social' => $cliente->razao_social,
                    'cnpj' => $cliente->cnpj,
                    'municipio_codigo' => $cliente->municipio_codigo,
                    'tabela_preco_nivel' => $tabelaCliente?->tabela_preco_nome,
                    'telefone' => $telefonePrincipal,
                    'email' => $emailPrincipal,
                    'telefones' => $telefones->all(),
                    'emails' => $emails->all(),
                    'contatos' => $contatos->all(),
                ];
            });

        $produtos = Produtos::with(['imagens' => function ($query) {
                $query->orderBy('ordem')->orderBy('id');
            }])
            ->where('empresa_id', $empresa)
            ->where('ativo', true)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'codigo', 'nome', 'preco_tabela', 'preco_minimo', 'multiplo', 'peso_bruto', 'unidade', 'ipi', 'st'])
            ->map(function ($produto) {
                return [
                    'id' => $produto->id,
                    'codigo' => $produto->codigo,
                    'nome' => $produto->nome,
                    'preco_tabela' => $produto->preco_tabela,
                    'preco_minimo' => $produto->preco_minimo,
                    'multiplo' => $produto->multiplo,
                    'peso_bruto' => $produto->peso_bruto,
                    'unidade' => $produto->unidade,
                    'ipi' => $produto->ipi,
                    'st' => $produto->st,
                    'imagem_base64' => $produto->imagens->first()?->imagem_base64,
                ];
            });

        $formasPagamentos = FormasPagamentos::where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $condicoesPagamentos = CondicoesPagamentos::where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome', 'valor_minimo']);

        $tiposPedidos = TiposPedidos::where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return [
            'clientes' => $clientes,
            'produtos' => $produtos,
            'formas_pagamentos' => $formasPagamentos,
            'condicoes_pagamentos' => $condicoesPagamentos,
            'tipos_pedidos' => $tiposPedidos,
        ];
    }

    private function regrasValidacao(int|string $empresa): array
    {
        return [
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('empresa_id', $empresa)],
            'tipo_pedido_id' => ['nullable', Rule::exists('tipos_pedidos', 'id')->where('empresa_id', $empresa)],
            'forma_pagamento_id' => ['nullable', Rule::exists('formas_pagamentos', 'id')->where('empresa_id', $empresa)],
            'condicao_pagamento_id' => ['nullable', Rule::exists('condicoes_pagamentos', 'id')->where('empresa_id', $empresa)],
            'status' => ['required', 'in:pendente,aprovado,cancelado'],
            'valor_frete' => ['nullable', 'numeric', 'min:0'],
            'data_emissao' => ['nullable', 'date'],
            'contato_cliente' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => ['required', Rule::exists('produtos', 'id')->where('empresa_id', $empresa)],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.preco_tabela' => ['nullable', 'numeric', 'min:0'],
            'itens.*.preco_liquido' => ['nullable', 'numeric', 'min:0'],
            'itens.*.subtotal' => ['nullable', 'numeric', 'min:0'],
            'itens.*.item_desconto' => ['nullable', 'array'],
            'itens.*.item_acrescimo' => ['nullable', 'array'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ];
    }

    private function salvarPedido(array $validated, int|string $empresa, ?Pedidos $pedido = null): Pedidos
    {
        return DB::transaction(function () use ($validated, $empresa, $pedido) {
            $totalItens = 0;
            $valorFrete = (float) ($validated['valor_frete'] ?? 0);

            if (!$pedido) {
                $pedido = new Pedidos();
                $pedido->empresa_id = (int) $empresa;
                $pedido->criador_id = Auth::id();
                $pedido->data_criacao = now();
            }

            $pedido->cliente_id = $validated['cliente_id'];
            $pedido->forma_pagamento_id = $validated['forma_pagamento_id'] ?? null;
            $pedido->condicao_pagamento_id = $validated['condicao_pagamento_id'] ?? null;
            $pedido->tipo_pedido_id = $validated['tipo_pedido_id'] ?? null;
            $pedido->status = $validated['status'];
            $pedido->valor_frete = $valorFrete;
            $pedido->nome_contato = $validated['contato_cliente'] ?? null;
            $pedido->observacoes = $validated['observacoes'] ?? null;
            $pedido->data_emissao = $validated['data_emissao'] ?? now()->toDateString();
            $pedido->ultima_alteracao = now();
            $pedido->total = 0;
            $pedido->save();

            PedidosItens::where('pedido_id', $pedido->id)->delete();

            foreach ($validated['itens'] as $item) {
                $produto = Produtos::where('empresa_id', $empresa)->findOrFail($item['produto_id']);

                $precoTabela = (float) ($item['preco_tabela'] ?? $produto->preco_tabela ?? 0);
                $precoLiquido = (float) ($item['preco_liquido'] ?? $precoTabela);
                $quantidade = (int) $item['quantidade'];
                $subtotal = (float) ($item['subtotal'] ?? ($precoLiquido * $quantidade));
                $totalItens += $subtotal;

                PedidosItens::create([
                    'empresa_id' => (int) $empresa,
                    'pedido_id' => $pedido->id,
                    'produto_id' => $produto->id,
                    'preco_tabela' => $precoTabela,
                    'preco_liquido' => $precoLiquido,
                    'ipi' => $produto->ipi ?? 0,
                    'st' => $produto->st ?? 0,
                    'subtotal' => $subtotal,
                    'quantidade' => $quantidade,
                    'descontos_do_vendedor' => $item['item_desconto'] ?? [],
                    'descontos_de_promocoes' => $item['item_acrescimo'] ?? [],
                    'observacoes' => $item['observacoes'] ?? null,
                ]);
            }

            $pedido->total = $totalItens + $valorFrete;
            $pedido->save();

            return $pedido;
        });
    }

    public function index($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $pedidos = Pedidos::with(['itens', 'cliente', 'usuario.roles', 'tipo_pedido'])
            ->where('empresa_id', $empresa)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pedido) {
                $pedido->total = $pedido->itens->sum('subtotal');
                $referenciaData = $pedido->created_at ?? $pedido->data_criacao;
                $pedido->criado_em = $referenciaData ? Carbon::parse($referenciaData)->format('d/m/Y H:i') : null;
                $pedido->foi_enviado = $pedido->status === 'aprovado' || $pedido->status_faturamento === 'faturado';
                $pedido->enviado_label = $pedido->foi_enviado ? 'Sim' : 'Nao';
                return $pedido;
            });

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'empresa_id' => (int) $empresa,
        ]);
    }

    public function create($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Pedidos/Create', [
            'empresa_id' => (int) $empresa,
            ...$this->carregarDadosFormulario($empresa),
            'export_config' => $this->obterExportConfig($empresa),
            'pedido' => null,
            'is_edit' => false,
        ]);
    }

    public function store(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate($this->regrasValidacao($empresa));
        $pedido = $this->salvarPedido($validated, $empresa);

        return redirect("/{$empresa}/pedidos")->with('success', "Pedido #{$pedido->id} criado com sucesso.");
    }

    public function edit($empresa, $pedido)
    {
        $this->validarAcessoEmpresa($empresa);

        $pedidoModel = Pedidos::with(['usuario', 'cliente.telefones', 'cliente.emails', 'cliente.contatos.telefones', 'cliente.contatos.emails', 'itens.produto.imagens'])
            ->where('empresa_id', $empresa)
            ->findOrFail($pedido);

        $tabelaCliente = null;
        if ($pedidoModel->cliente?->municipio_codigo) {
            $tabelaCliente = TabelasPrecosCidades::query()
                ->select('tabelas_precos.nome as tabela_preco_nome')
                ->join('tabelas_precos', 'tabelas_precos.id', '=', 'tabelas_precos_cidades.tabela_preco_id')
                ->where('tabelas_precos_cidades.empresa_id', $empresa)
                ->where('tabelas_precos_cidades.municipio_codigo', $pedidoModel->cliente->municipio_codigo)
                ->where('tabelas_precos_cidades.excluido', false)
                ->where('tabelas_precos.excluido', false)
                ->value('tabela_preco_nome');
        }

        $pedidoPayload = [
            'id' => $pedidoModel->id,
            'cliente_id' => $pedidoModel->cliente_id,
            'tipo_pedido_id' => $pedidoModel->tipo_pedido_id,
            'forma_pagamento_id' => $pedidoModel->forma_pagamento_id,
            'condicao_pagamento_id' => $pedidoModel->condicao_pagamento_id,
            'valor_frete' => (float) ($pedidoModel->valor_frete ?? 0),
            'data_emissao' => optional($pedidoModel->data_emissao)->format('Y-m-d'),
            'contato_cliente' => $pedidoModel->nome_contato,
            'observacoes' => $pedidoModel->observacoes,
            'status' => $pedidoModel->status ?: 'pendente',
            'vendedor' => $pedidoModel->usuario?->name,
            'cliente' => [
                'id' => $pedidoModel->cliente?->id,
                'razao_social' => $pedidoModel->cliente?->razao_social,
                'nome_fantasia' => $pedidoModel->cliente?->nome_fantasia,
                'cnpj' => $pedidoModel->cliente?->cnpj,
                'telefone' => $pedidoModel->cliente?->telefones?->pluck('numero')?->filter()?->first(),
                'email' => $pedidoModel->cliente?->emails?->pluck('email')?->filter()?->first(),
                'telefones' => $pedidoModel->cliente?->telefones?->pluck('numero')?->filter()?->values() ?? [],
                'emails' => $pedidoModel->cliente?->emails?->pluck('email')?->filter()?->values() ?? [],
                'contatos' => $pedidoModel->cliente?->contatos?->map(function ($contato) {
                    return [
                        'id' => $contato->id,
                        'nome' => $contato->nome,
                        'cargo' => $contato->cargo,
                        'telefones' => $contato->telefones?->pluck('numero')?->filter()?->values() ?? [],
                        'emails' => $contato->emails?->pluck('email')?->filter()?->values() ?? [],
                    ];
                })->values() ?? [],
                'tabela_preco_nivel' => $tabelaCliente,
            ],
            'itens' => $pedidoModel->itens->map(function ($item) {
                return [
                    'produto_id' => $item->produto_id,
                    'nome' => $item->produto?->nome,
                    'codigo' => $item->produto?->codigo,
                    'imagem_base64' => $item->produto?->imagens?->first()?->imagem_base64,
                    'unidade' => $item->produto?->unidade ?: 'UN',
                    'multiplo' => (int) ($item->produto?->multiplo ?? 1),
                    'peso_bruto' => (float) ($item->produto?->peso_bruto ?? 0),
                    'quantidade' => (int) $item->quantidade,
                    'preco_tabela' => (float) ($item->preco_tabela ?? 0),
                    'preco_liquido' => (float) ($item->preco_liquido ?? 0),
                    'st' => (float) ($item->st ?? 0),
                    'subtotal' => (float) ($item->subtotal ?? 0),
                    'item_desconto' => $item->descontos_do_vendedor ?? [],
                    'item_acrescimo' => $item->descontos_de_promocoes ?? [],
                    'observacoes' => $item->observacoes,
                ];
            })->values(),
        ];

        return Inertia::render('Pedidos/Create', [
            'empresa_id' => (int) $empresa,
            ...$this->carregarDadosFormulario($empresa),
            'export_config' => $this->obterExportConfig($empresa),
            'pedido' => $pedidoPayload,
            'is_edit' => true,
        ]);
    }

    public function update(Request $request, $empresa, $pedido)
    {
        $this->validarAcessoEmpresa($empresa);

        $pedidoModel = Pedidos::where('empresa_id', $empresa)->findOrFail($pedido);
        $validated = $request->validate($this->regrasValidacao($empresa));

        $this->salvarPedido($validated, $empresa, $pedidoModel);

        return redirect("/{$empresa}/pedidos")->with('success', "Pedido #{$pedidoModel->id} atualizado com sucesso.");
    }

    public function salvarConfiguracaoExportacao(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'formato' => ['required', 'in:xls,csv,txt'],
            'incluir_cabecalho_colunas' => ['required', 'boolean'],
            'incluir_dados_cabecalho' => ['required', 'boolean'],
            'incluir_itens' => ['required', 'boolean'],
            'incluir_informacoes_extras' => ['required', 'boolean'],
            'colunas' => ['required', 'array', 'min:1'],
            'colunas.*' => ['required', Rule::in(self::EXPORT_COLUMN_OPTIONS)],
        ]);

        $config = $this->sanitizeExportConfig($validated);

        PedidosExportacaoConfiguracoes::updateOrCreate(
            [
                'empresa_id' => (int) $empresa,
                'user_id' => (int) Auth::id(),
            ],
            [
                'configuracoes' => $config,
            ]
        );

        return response()->json(['ok' => true, 'config' => $config]);
    }

    public function exportar(Request $request, $empresa, $pedido): StreamedResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $pedidoModel = Pedidos::with(['cliente.telefones', 'cliente.emails', 'itens.produto', 'extras'])
            ->where('empresa_id', $empresa)
            ->findOrFail($pedido);

        $config = $this->obterExportConfig($empresa);
        $format = $request->query('formato');
        if (is_string($format) && in_array($format, ['xls', 'csv', 'txt'], true)) {
            $config['formato'] = $format;
        }

        $delimiter = match ($config['formato']) {
            'xls' => "\t",
            'txt' => '|',
            default => ';',
        };

        $mime = match ($config['formato']) {
            'xls' => 'application/vnd.ms-excel',
            'txt' => 'text/plain; charset=UTF-8',
            default => 'text/csv; charset=UTF-8',
        };

        $filename = "pedido-{$pedidoModel->id}.{$config['formato']}";

        $colunasMap = [
            'ordem' => 'Ordem',
            'codigo' => 'Codigo',
            'descricao' => 'Descricao',
            'quantidade' => 'Quantidade',
            'unidade' => 'Unidade',
            'desconto' => 'Desconto',
            'acrescimo' => 'Acrescimo',
            'preco_tabela' => 'Preco Tabela',
            'preco_liquido' => 'Preco Liquido',
            'st' => 'ST',
            'subtotal' => 'Subtotal',
            'observacoes' => 'Observacoes',
        ];

        $selectedColumns = collect($config['colunas'])
            ->filter(fn ($coluna) => array_key_exists($coluna, $colunasMap))
            ->values()
            ->all();

        return response()->streamDownload(function () use ($pedidoModel, $config, $selectedColumns, $colunasMap, $delimiter) {
            $out = fopen('php://output', 'w');

            if ($config['incluir_dados_cabecalho']) {
                $headerRows = [
                    ['Pedido', (string) $pedidoModel->id],
                    ['Cliente', (string) ($pedidoModel->cliente?->razao_social ?? '')],
                    ['CNPJ', (string) ($pedidoModel->cliente?->cnpj ?? '')],
                    ['Contato', (string) ($pedidoModel->nome_contato ?? '')],
                    ['Telefone', (string) ($pedidoModel->cliente?->telefones?->pluck('numero')->filter()->first() ?? '')],
                    ['E-mail', (string) ($pedidoModel->cliente?->emails?->pluck('email')->filter()->first() ?? '')],
                    ['Data Emissao', (string) optional($pedidoModel->data_emissao)->format('d/m/Y')],
                    ['Observacoes', (string) ($pedidoModel->observacoes ?? '')],
                ];

                foreach ($headerRows as $row) {
                    fputcsv($out, $row, $delimiter);
                }
                fputcsv($out, [''], $delimiter);
            }

            if ($config['incluir_itens']) {
                if ($config['incluir_cabecalho_colunas']) {
                    $itemHeader = array_map(fn ($coluna) => $colunasMap[$coluna], $selectedColumns);
                    fputcsv($out, $itemHeader, $delimiter);
                }

                foreach ($pedidoModel->itens as $index => $item) {
                    $descontos = collect($item->descontos_do_vendedor ?? [])->filter()->implode(' + ');
                    $acrescimos = collect($item->descontos_de_promocoes ?? [])->filter()->implode(' + ');
                    $rowMap = [
                        'ordem' => (string) ($index + 1),
                        'codigo' => (string) ($item->produto?->codigo ?? ''),
                        'descricao' => (string) ($item->produto?->nome ?? ''),
                        'quantidade' => (string) ((int) $item->quantidade),
                        'unidade' => (string) ($item->produto?->unidade ?? 'UN'),
                        'desconto' => (string) $descontos,
                        'acrescimo' => (string) $acrescimos,
                        'preco_tabela' => number_format((float) ($item->preco_tabela ?? 0), 2, ',', '.'),
                        'preco_liquido' => number_format((float) ($item->preco_liquido ?? 0), 2, ',', '.'),
                        'st' => number_format((float) ($item->st ?? 0), 2, ',', '.') . '%',
                        'subtotal' => number_format((float) ($item->subtotal ?? 0), 2, ',', '.'),
                        'observacoes' => (string) ($item->observacoes ?? ''),
                    ];

                    $row = array_map(fn ($coluna) => $rowMap[$coluna] ?? '', $selectedColumns);
                    fputcsv($out, $row, $delimiter);
                }
            }

            if ($config['incluir_informacoes_extras'] && $pedidoModel->extras->isNotEmpty()) {
                fputcsv($out, [''], $delimiter);
                fputcsv($out, ['Campo Extra', 'Valor'], $delimiter);

                foreach ($pedidoModel->extras as $extra) {
                    $value = $extra->valor_texto
                        ?? $extra->valor_data
                        ?? $extra->valor_decimal
                        ?? $extra->valor_hora
                        ?? (is_array($extra->valor_lista) ? implode(', ', $extra->valor_lista) : '');

                    fputcsv($out, [(string) $extra->nome, (string) $value], $delimiter);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => $mime]);
    }

    public function destroy($empresa, $pedido)
    {
        $this->validarAcessoEmpresa($empresa);

        $pedidoModel = Pedidos::where('empresa_id', $empresa)->findOrFail($pedido);
        $pedidoModel->delete();

        return redirect("/{$empresa}/pedidos")->with('success', "Pedido #{$pedido} removido com sucesso.");
    }
}
