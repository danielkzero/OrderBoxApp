<?php

namespace App\Http\Controllers;

use App\Models\CidadesIbge;
use App\Models\Clientes;
use App\Models\ClientesCamposExtrasConfiguracoes;
use App\Models\ClientesConfiguracoesGerais;
use App\Models\ClientesContatos;
use App\Models\ClientesContatosEmails;
use App\Models\ClientesContatosTelefones;
use App\Models\ClientesEmails;
use App\Models\ClientesEnderecos;
use App\Models\ClientesExcecoesFiscais;
use App\Models\ClientesExtras;
use App\Models\ClientesRedes;
use App\Models\ClientesSegmentos;
use App\Models\ClientesTags;
use App\Models\ClientesTelefones;
use App\Models\Icms_st;
use App\Models\MotivosBloqueios;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClientesController extends Controller
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

    private function obterConfiguracoesGerais(int|string $empresa): ClientesConfiguracoesGerais
    {
        return ClientesConfiguracoesGerais::query()->firstOrCreate(
            ['empresa_id' => (int) $empresa],
            [
                'bloquear_duplicidade_cpf_cnpj' => false,
                'obrigar_cpf_cnpj' => false,
                'obrigar_nome_fantasia' => false,
                'obrigar_telefone' => false,
                'obrigar_email' => false,
                'obrigar_inscricao_estadual' => false,
                'obrigar_info_adicional' => false,
                'obrigar_segmento' => false,
                'obrigar_cep' => false,
                'obrigar_endereco' => false,
                'obrigar_numero' => false,
                'obrigar_complemento' => false,
                'obrigar_bairro' => false,
                'obrigar_cidade' => false,
                'obrigar_estado' => false,
            ]
        );
    }

    private function carregarOpcoesFormulario(int|string $empresa): array
    {
        $cidades = CidadesIbge::query()
            ->select(['municipio_codigo', 'municipio_nome', 'uf_codigo', 'uf_nome'])
            ->orderBy('uf_nome')
            ->orderBy('municipio_nome')
            ->get();

        $estados = $cidades
            ->map(fn ($cidade) => ['codigo' => $cidade->uf_codigo, 'nome' => $cidade->uf_nome])
            ->unique('codigo')
            ->values();

        $municipiosPorUf = $cidades
            ->groupBy('uf_codigo')
            ->map(fn ($items) => $items
                ->map(fn ($cidade) => [
                    'codigo' => $cidade->municipio_codigo,
                    'nome' => $cidade->municipio_nome,
                ])
                ->values())
            ->toArray();

        $icmsOptions = Icms_st::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome_excecao_fiscal')
            ->get(['id', 'nome_excecao_fiscal'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'nome' => $item->nome_excecao_fiscal,
            ])
            ->values();

        return [
            'empresa_id' => (int) $empresa,
            'config_geral' => $this->obterConfiguracoesGerais($empresa),
            'icms_options' => $icmsOptions,
            'tags' => ClientesTags::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'segmentos' => ClientesSegmentos::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'redes' => ClientesRedes::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'excecoes_fiscais' => ClientesExcecoesFiscais::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'motivos_bloqueio' => MotivosBloqueios::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'campos_extras_config' => ClientesCamposExtrasConfiguracoes::where('empresa_id', $empresa)
                ->where('excluido', false)
                ->where('ativo', true)
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(['id', 'nome', 'tipo', 'obrigatorio', 'opcoes']),
            'estados' => $estados,
            'municipios_por_uf' => $municipiosPorUf,
        ];
    }

    private function clienteParaFormulario(Clientes $cliente): array
    {
        $municipioPrincipal = CidadesIbge::query()
            ->where('municipio_codigo', $cliente->municipio_codigo)
            ->first(['uf_codigo']);

        $extras = $cliente->campos_extras
            ->keyBy('campo_extra_id')
            ->map(function ($extra) {
                if ($extra->tipo === 'DATA') {
                    return $extra->valor_data;
                }

                if ($extra->tipo === 'NUMERICO') {
                    return $extra->valor_decimal;
                }

                return $extra->valor_texto;
            });

        return [
            'id' => $cliente->id,
            'tipo' => $cliente->tipo,
            'razao_social' => $cliente->razao_social,
            'nome_fantasia' => $cliente->nome_fantasia,
            'cnpj' => $cliente->cnpj,
            'inscricao_estadual' => $cliente->inscricao_estadual,
            'suframa' => $cliente->suframa,
            'icms_st_id' => $cliente->icms_st_id,
            'tags_ids' => $cliente->tags->pluck('id')->whenEmpty(fn ($collection) => $cliente->tag_id ? collect([$cliente->tag_id]) : collect())->values(),
            'segmento_id' => $cliente->segmento_id,
            'rede_id' => $cliente->rede_id,
            'excecao_fiscal_id' => $cliente->excecao_fiscal_id,
            'bloqueado' => (bool) $cliente->bloqueado,
            'motivo_bloqueio_id' => $cliente->motivo_bloqueio_id,
            'observacao' => $cliente->observacao,
            'telefones' => $cliente->telefones->map(fn ($telefone) => [
                'numero' => $telefone->numero,
                'tipo' => $telefone->tipo,
            ])->values(),
            'emails' => $cliente->emails->map(fn ($email) => [
                'email' => $email->email,
                'tipo' => $email->tipo,
            ])->values(),
            'endereco_principal' => [
                'cep' => $cliente->cep,
                'rua' => $cliente->rua,
                'numero' => $cliente->numero,
                'complemento' => $cliente->complemento,
                'bairro' => $cliente->bairro,
                'municipio_codigo' => $cliente->municipio_codigo,
                'uf_codigo' => $municipioPrincipal?->uf_codigo,
            ],
            'enderecos_adicionais' => $cliente->enderecos->map(function ($endereco) {
                return [
                    'cep' => $endereco->cep,
                    'rua' => $endereco->rua,
                    'numero' => $endereco->numero,
                    'complemento' => $endereco->complemento,
                    'bairro' => $endereco->bairro,
                    'municipio_codigo' => $endereco->municipio_codigo,
                    'uf_codigo' => $endereco->ibge?->uf_codigo,
                ];
            })->values(),
            'contatos' => $cliente->contatos->map(function ($contato) {
                return [
                    'nome' => $contato->nome,
                    'cargo' => $contato->cargo,
                    'telefones' => $contato->telefones->map(fn ($telefone) => [
                        'numero' => $telefone->numero,
                        'tipo' => $telefone->tipo,
                    ])->values(),
                    'emails' => $contato->emails->map(fn ($email) => [
                        'email' => $email->email,
                        'tipo' => $email->tipo,
                    ])->values(),
                ];
            })->values(),
            'campos_extras' => $extras,
        ];
    }

    private function regrasValidacao(int|string $empresa, ClientesConfiguracoesGerais $config, ?int $clienteId = null): array
    {
        return [
            'tipo' => ['required', Rule::in(['J', 'F'])],
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => [($config->obrigar_nome_fantasia ? 'required' : 'nullable'), 'string', 'max:255'],
            'cnpj' => [($config->obrigar_cpf_cnpj ? 'required' : 'nullable'), 'string', 'max:20'],
            'inscricao_estadual' => [($config->obrigar_inscricao_estadual ? 'required' : 'nullable'), 'string', 'max:50'],
            'suframa' => ['nullable', 'string', 'max:50'],
            'icms_st_id' => ['required', Rule::exists('icms_st', 'id')->where('empresa_id', $empresa)],
            'tags_ids' => ['nullable', 'array'],
            'tags_ids.*' => [Rule::exists('clientes_tags', 'id')->where('empresa_id', $empresa)->where('excluido', false)],
            'segmento_id' => [($config->obrigar_segmento ? 'required' : 'nullable'), Rule::exists('clientes_segmentos', 'id')->where('empresa_id', $empresa)->where('excluido', false)],
            'rede_id' => ['nullable', Rule::exists('clientes_redes', 'id')->where('empresa_id', $empresa)->where('excluido', false)],
            'excecao_fiscal_id' => ['nullable', Rule::exists('clientes_excecoes_fiscais', 'id')->where('empresa_id', $empresa)->where('excluido', false)],
            'bloqueado' => ['required', 'boolean'],
            'motivo_bloqueio_id' => ['nullable', Rule::exists('motivos_bloqueios', 'id')->where('empresa_id', $empresa)->where('excluido', false)],
            'observacao' => [($config->obrigar_info_adicional ? 'required' : 'nullable'), 'string'],

            'telefones' => ['nullable', 'array'],
            'telefones.*.numero' => ['nullable', 'string', 'max:20'],
            'telefones.*.tipo' => ['nullable', 'string', 'max:1'],
            'emails' => ['nullable', 'array'],
            'emails.*.email' => ['nullable', 'email:rfc', 'max:255'],
            'emails.*.tipo' => ['nullable', 'string', 'max:1'],

            'endereco_principal' => ['required', 'array'],
            'endereco_principal.cep' => [($config->obrigar_cep ? 'required' : 'nullable'), 'string', 'max:20'],
            'endereco_principal.rua' => [($config->obrigar_endereco ? 'required' : 'nullable'), 'string', 'max:255'],
            'endereco_principal.numero' => [($config->obrigar_numero ? 'required' : 'nullable'), 'string', 'max:20'],
            'endereco_principal.complemento' => [($config->obrigar_complemento ? 'required' : 'nullable'), 'string', 'max:255'],
            'endereco_principal.bairro' => [($config->obrigar_bairro ? 'required' : 'nullable'), 'string', 'max:255'],
            'endereco_principal.municipio_codigo' => [($config->obrigar_cidade || $config->obrigar_estado ? 'required' : 'nullable'), Rule::exists('cidades_ibge', 'municipio_codigo')],

            'enderecos_adicionais' => ['nullable', 'array'],
            'enderecos_adicionais.*.cep' => ['nullable', 'string', 'max:20'],
            'enderecos_adicionais.*.rua' => ['nullable', 'string', 'max:255'],
            'enderecos_adicionais.*.numero' => ['nullable', 'string', 'max:20'],
            'enderecos_adicionais.*.complemento' => ['nullable', 'string', 'max:255'],
            'enderecos_adicionais.*.bairro' => ['nullable', 'string', 'max:255'],
            'enderecos_adicionais.*.municipio_codigo' => ['nullable', Rule::exists('cidades_ibge', 'municipio_codigo')],

            'contatos' => ['nullable', 'array'],
            'contatos.*.nome' => ['nullable', 'string', 'max:255'],
            'contatos.*.cargo' => ['nullable', 'string', 'max:255'],
            'contatos.*.telefones' => ['nullable', 'array'],
            'contatos.*.telefones.*.numero' => ['nullable', 'string', 'max:20'],
            'contatos.*.emails' => ['nullable', 'array'],
            'contatos.*.emails.*.email' => ['nullable', 'email:rfc', 'max:255'],

            'campos_extras' => ['nullable', 'array'],
        ];
    }

    private function validarRegrasNegocio(array $validated, int|string $empresa, ClientesConfiguracoesGerais $config, ?int $clienteId = null): void
    {
        if ($config->bloquear_duplicidade_cpf_cnpj && !empty($validated['cnpj'])) {
            $query = Clientes::query()
                ->where('empresa_id', $empresa)
                ->where('excluido', false)
                ->where('cnpj', $validated['cnpj']);

            if ($clienteId) {
                $query->where('id', '!=', $clienteId);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'cnpj' => 'Ja existe cliente cadastrado com esse CPF/CNPJ.',
                ]);
            }
        }

        if ($config->obrigar_telefone) {
            $temTelefone = collect($validated['telefones'] ?? [])->pluck('numero')->filter()->isNotEmpty();
            if (!$temTelefone) {
                throw ValidationException::withMessages([
                    'telefones' => 'Telefone e obrigatorio pelas configuracoes gerais.',
                ]);
            }
        }

        if ($config->obrigar_email) {
            $temEmail = collect($validated['emails'] ?? [])->pluck('email')->filter()->isNotEmpty();
            if (!$temEmail) {
                throw ValidationException::withMessages([
                    'emails' => 'E-mail e obrigatorio pelas configuracoes gerais.',
                ]);
            }
        }
    }

    private function salvarCliente(
        array $validated,
        int|string $empresa,
        ClientesConfiguracoesGerais $config,
        ?Clientes $cliente = null
    ): Clientes {
        $this->validarRegrasNegocio($validated, $empresa, $config, $cliente?->id);

        $camposConfig = ClientesCamposExtrasConfiguracoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->where('ativo', true)
            ->get()
            ->keyBy('id');

        $extrasInput = Arr::get($validated, 'campos_extras', []);
        $errosCamposExtras = [];
        foreach ($camposConfig as $campoConfig) {
            if (!$campoConfig->obrigatorio) {
                continue;
            }

            $valor = $extrasInput[(string) $campoConfig->id] ?? null;
            if (is_array($valor)) {
                $valor = implode('', array_filter($valor));
            }

            if ($valor === null || $valor === '') {
                $errosCamposExtras["campos_extras.{$campoConfig->id}"] = "O campo extra {$campoConfig->nome} e obrigatorio.";
            }
        }

        if (!empty($errosCamposExtras)) {
            throw ValidationException::withMessages($errosCamposExtras);
        }

        return DB::transaction(function () use ($validated, $empresa, $cliente, $camposConfig) {
            if (!$cliente) {
                $cliente = new Clientes();
                $cliente->empresa_id = (int) $empresa;
                $cliente->excluido = false;
            }

            $principal = $validated['endereco_principal'];

            $cliente->fill([
                'tipo' => $validated['tipo'],
                'razao_social' => $validated['razao_social'],
                'nome_fantasia' => $validated['nome_fantasia'] ?? null,
                'cnpj' => $validated['cnpj'] ?? null,
                'inscricao_estadual' => $validated['inscricao_estadual'] ?? null,
                'suframa' => $validated['suframa'] ?? null,
                'icms_st_id' => $validated['icms_st_id'],
                'tag_id' => collect($validated['tags_ids'] ?? [])->filter()->first(),
                'segmento_id' => $validated['segmento_id'] ?? null,
                'rede_id' => $validated['rede_id'] ?? null,
                'excecao_fiscal_id' => $validated['excecao_fiscal_id'] ?? null,
                'rua' => $principal['rua'] ?? null,
                'numero' => $principal['numero'] ?? null,
                'complemento' => $principal['complemento'] ?? null,
                'bairro' => $principal['bairro'] ?? null,
                'cep' => $principal['cep'] ?? null,
                'municipio_codigo' => $principal['municipio_codigo'] ?? null,
                'bloqueado' => (bool) $validated['bloqueado'],
                'motivo_bloqueio_id' => ($validated['bloqueado'] ?? false) ? ($validated['motivo_bloqueio_id'] ?? null) : null,
                'observacao' => $validated['observacao'] ?? null,
                'ultima_alteracao' => now(),
            ]);

            $cliente->save();

            $tagIds = collect($validated['tags_ids'] ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();
            $cliente->tags()->sync($tagIds);
            $cliente->tag_id = $tagIds[0] ?? null;
            $cliente->save();

            ClientesTelefones::where('empresa_id', $empresa)->where('cliente_id', $cliente->id)->delete();
            foreach (($validated['telefones'] ?? []) as $telefone) {
                if (!empty($telefone['numero'])) {
                    ClientesTelefones::create([
                        'empresa_id' => (int) $empresa,
                        'cliente_id' => $cliente->id,
                        'numero' => $telefone['numero'],
                        'tipo' => $telefone['tipo'] ?? null,
                        'ultima_alteracao' => now(),
                    ]);
                }
            }

            ClientesEmails::where('empresa_id', $empresa)->where('cliente_id', $cliente->id)->delete();
            foreach (($validated['emails'] ?? []) as $email) {
                if (!empty($email['email'])) {
                    ClientesEmails::create([
                        'empresa_id' => (int) $empresa,
                        'cliente_id' => $cliente->id,
                        'email' => $email['email'],
                        'tipo' => $email['tipo'] ?? null,
                        'ultima_alteracao' => now(),
                    ]);
                }
            }

            ClientesEnderecos::where('empresa_id', $empresa)->where('cliente_id', $cliente->id)->delete();
            foreach (($validated['enderecos_adicionais'] ?? []) as $endereco) {
                $temDados = collect($endereco)->filter()->isNotEmpty();
                if (!$temDados) {
                    continue;
                }

                ClientesEnderecos::create([
                    'empresa_id' => (int) $empresa,
                    'cliente_id' => $cliente->id,
                    'rua' => $endereco['rua'] ?? null,
                    'numero' => $endereco['numero'] ?? null,
                    'complemento' => $endereco['complemento'] ?? null,
                    'bairro' => $endereco['bairro'] ?? null,
                    'cep' => $endereco['cep'] ?? null,
                    'municipio_codigo' => $endereco['municipio_codigo'] ?? null,
                    'ultima_alteracao' => now(),
                ]);
            }

            ClientesContatos::where('empresa_id', $empresa)->where('cliente_id', $cliente->id)->delete();
            foreach (($validated['contatos'] ?? []) as $contatoData) {
                $temDadosContato = !empty($contatoData['nome'])
                    || !empty($contatoData['cargo'])
                    || collect($contatoData['telefones'] ?? [])->pluck('numero')->filter()->isNotEmpty()
                    || collect($contatoData['emails'] ?? [])->pluck('email')->filter()->isNotEmpty();

                if (!$temDadosContato) {
                    continue;
                }

                $contato = ClientesContatos::create([
                    'empresa_id' => (int) $empresa,
                    'cliente_id' => $cliente->id,
                    'nome' => $contatoData['nome'] ?: 'Contato',
                    'cargo' => $contatoData['cargo'] ?? null,
                    'excluido' => false,
                ]);

                foreach (($contatoData['telefones'] ?? []) as $telefone) {
                    if (!empty($telefone['numero'])) {
                        ClientesContatosTelefones::create([
                            'empresa_id' => (int) $empresa,
                            'cliente_contato_id' => $contato->id,
                            'numero' => $telefone['numero'],
                            'tipo' => $telefone['tipo'] ?? null,
                        ]);
                    }
                }

                foreach (($contatoData['emails'] ?? []) as $email) {
                    if (!empty($email['email'])) {
                        ClientesContatosEmails::create([
                            'empresa_id' => (int) $empresa,
                            'cliente_contato_id' => $contato->id,
                            'email' => $email['email'],
                            'tipo' => $email['tipo'] ?? null,
                        ]);
                    }
                }
            }

            ClientesExtras::where('empresa_id', $empresa)->where('cliente_id', $cliente->id)->delete();
            $extrasInput = Arr::get($validated, 'campos_extras', []);

            foreach ($camposConfig as $campoConfig) {
                $campoId = (string) $campoConfig->id;
                $valor = $extrasInput[$campoId] ?? null;

                if (is_array($valor)) {
                    $valor = implode(', ', array_filter($valor));
                }

                if ($valor === null || $valor === '') {
                    continue;
                }

                $payload = [
                    'empresa_id' => (int) $empresa,
                    'cliente_id' => $cliente->id,
                    'campo_extra_id' => $campoConfig->id,
                    'nome' => $campoConfig->nome,
                    'tipo' => $campoConfig->tipo,
                    'valor_texto' => null,
                    'valor_data' => null,
                    'valor_decimal' => null,
                    'nome_arquivo' => null,
                    'valor_arquivo' => null,
                ];

                if ($campoConfig->tipo === 'DATA') {
                    $payload['valor_data'] = $valor;
                } elseif ($campoConfig->tipo === 'NUMERICO') {
                    $payload['valor_decimal'] = is_numeric($valor) ? (float) $valor : null;
                } else {
                    $payload['valor_texto'] = (string) $valor;
                }

                ClientesExtras::create($payload);
            }

            return $cliente;
        });
    }

    public function index($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $clientes = Clientes::with(['telefones', 'emails', 'enderecos', 'pedidos', 'ibge', 'tags'])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('razao_social')
            ->get();

        $ativos = 0;
        $inativosRecentes = 0;
        $inativosAntigos = 0;
        $prospects = 0;

        foreach ($clientes as $cliente) {
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
            } elseif ($mesesDesdeUltimoPedido < 12) {
                $inativosRecentes++;
                $cliente->status = 'inativo_recente';
            } else {
                $inativosAntigos++;
                $cliente->status = 'inativo_antigo';
            }
        }

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'chartData' => [
                'series' => [$ativos, $inativosRecentes, $inativosAntigos, $prospects],
                'counts' => [
                    'ativos' => $ativos,
                    'inativos_recentes' => $inativosRecentes,
                    'inativos_antigos' => $inativosAntigos,
                    'prospects' => $prospects,
                ],
            ],
            'empresa' => (int) $empresa,
        ]);
    }

    public function vinculosPermissoes(Request $request, $empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $filtros = [
            'nome_cnpj' => (string) $request->query('nome_cnpj', ''),
            'cidade' => (string) $request->query('cidade', ''),
            'email' => (string) $request->query('email', ''),
            'estado' => (string) $request->query('estado', ''),
            'segmento_id' => $request->query('segmento_id') ? (int) $request->query('segmento_id') : null,
            'rede_id' => $request->query('rede_id') ? (int) $request->query('rede_id') : null,
            'tag_id' => $request->query('tag_id') ? (int) $request->query('tag_id') : null,
            'vendedor_id' => $request->query('vendedor_id') ? (int) $request->query('vendedor_id') : null,
            'representada_id' => $request->query('representada_id') ? (int) $request->query('representada_id') : null,
        ];

        $query = Clientes::query()
            ->with(['ibge', 'tags', 'emails'])
            ->where('empresa_id', $empresa)
            ->where('excluido', false);

        if ($filtros['nome_cnpj'] !== '') {
            $termo = mb_strtolower($filtros['nome_cnpj']);
            $query->where(function ($sub) use ($termo) {
                $sub->whereRaw('LOWER(razao_social) LIKE ?', ["%{$termo}%"])
                    ->orWhereRaw('LOWER(nome_fantasia) LIKE ?', ["%{$termo}%"])
                    ->orWhereRaw('LOWER(cnpj) LIKE ?', ["%{$termo}%"]);
            });
        }

        if ($filtros['cidade'] !== '') {
            $termoCidade = mb_strtolower($filtros['cidade']);
            $query->whereHas('ibge', function ($sub) use ($termoCidade) {
                $sub->whereRaw('LOWER(municipio_nome) LIKE ?', ["%{$termoCidade}%"]);
            });
        }

        if ($filtros['email'] !== '') {
            $termoEmail = mb_strtolower($filtros['email']);
            $query->whereHas('emails', function ($sub) use ($termoEmail) {
                $sub->whereRaw('LOWER(email) LIKE ?', ["%{$termoEmail}%"]);
            });
        }

        if ($filtros['estado'] !== '') {
            $query->whereHas('ibge', function ($sub) use ($filtros) {
                $sub->where('uf_codigo', $filtros['estado']);
            });
        }

        if ($filtros['segmento_id']) {
            $query->where('segmento_id', $filtros['segmento_id']);
        }

        if ($filtros['rede_id']) {
            $query->where('rede_id', $filtros['rede_id']);
        }

        if ($filtros['tag_id']) {
            $query->whereHas('tags', function ($sub) use ($filtros) {
                $sub->where('clientes_tags.id', $filtros['tag_id']);
            });
        }

        if ($filtros['vendedor_id']) {
            $query->whereHas('pedidos', function ($sub) use ($filtros) {
                $sub->where('criador_id', $filtros['vendedor_id']);
            });
        }

        if ($filtros['representada_id']) {
            $query->where('empresa_id', $filtros['representada_id']);
        }

        $clientes = $query
            ->orderBy('razao_social')
            ->limit(300)
            ->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'razao_social' => $cliente->razao_social,
                    'cidade' => $cliente->ibge?->municipio_nome,
                    'estado' => $cliente->ibge?->uf_codigo,
                    'tags' => $cliente->tags->pluck('nome')->values(),
                    'tabelas_preco' => '--',
                    'condicoes_pagamento' => '--',
                    'categorias' => '--',
                ];
            });

        $estados = CidadesIbge::query()
            ->select(['uf_codigo', 'uf_nome'])
            ->distinct()
            ->orderBy('uf_nome')
            ->get()
            ->map(fn ($estado) => ['codigo' => $estado->uf_codigo, 'nome' => $estado->uf_nome])
            ->values();

        $vendedores = Users::query()
            ->whereHas('empresas', fn ($sub) => $sub->where('empresas.id', (int) $empresa))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($user) => ['id' => $user->id, 'nome' => $user->name])
            ->values();

        $representadas = Auth::user()
            ->empresas()
            ->get(['empresas.id', 'empresas.nome'])
            ->map(fn ($empresaItem) => [
                'id' => $empresaItem->id,
                'nome' => $empresaItem->nome ?: "Empresa {$empresaItem->id}",
            ])
            ->values();

        return Inertia::render('Clientes/VinculosPermissoes', [
            'empresa_id' => (int) $empresa,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'estados' => $estados,
            'vendedores' => $vendedores,
            'segmentos' => ClientesSegmentos::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'redes' => ClientesRedes::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'tags' => ClientesTags::where('empresa_id', $empresa)->where('excluido', false)->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'representadas' => $representadas,
        ]);
    }

    public function create($empresa): Response
    {
        $this->validarAcessoEmpresa($empresa);

        return Inertia::render('Clientes/Form', [
            ...$this->carregarOpcoesFormulario($empresa),
            'cliente' => null,
            'is_edit' => false,
        ]);
    }

    public function store(Request $request, $empresa): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $config = $this->obterConfiguracoesGerais($empresa);
        $validated = $request->validate($this->regrasValidacao($empresa, $config));

        $cliente = $this->salvarCliente($validated, $empresa, $config);

        return redirect("/{$empresa}/clientes/{$cliente->id}/edit")
            ->with('success', 'Cliente salvo com sucesso.');
    }

    public function show($empresa, $cliente): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        return redirect("/{$empresa}/clientes/{$cliente}/edit");
    }

    public function edit($empresa, $cliente): Response
    {
        $this->validarAcessoEmpresa($empresa);

        $clienteModel = Clientes::with([
            'telefones',
            'emails',
            'enderecos.ibge',
            'contatos.telefones',
            'contatos.emails',
            'campos_extras',
            'tags',
        ])
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($cliente);

        return Inertia::render('Clientes/Form', [
            ...$this->carregarOpcoesFormulario($empresa),
            'cliente' => $this->clienteParaFormulario($clienteModel),
            'is_edit' => true,
        ]);
    }

    public function update(Request $request, $empresa, $cliente): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $clienteModel = Clientes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($cliente);

        $config = $this->obterConfiguracoesGerais($empresa);
        $validated = $request->validate($this->regrasValidacao($empresa, $config, (int) $clienteModel->id));

        $this->salvarCliente($validated, $empresa, $config, $clienteModel);

        return back()->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy($empresa, $cliente): RedirectResponse
    {
        $this->validarAcessoEmpresa($empresa);

        $clienteModel = Clientes::query()->where('empresa_id', $empresa)->findOrFail($cliente);
        $clienteModel->update([
            'excluido' => true,
            'ultima_alteracao' => now(),
        ]);

        return redirect("/{$empresa}/clientes")->with('success', 'Cliente excluido com sucesso.');
    }
}
