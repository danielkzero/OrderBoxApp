<?php

namespace App\Http\Controllers;

use App\Models\ClientesCamposExtrasConfiguracoes;
use App\Models\ClientesConfiguracoesGerais;
use App\Models\ClientesExcecoesFiscais;
use App\Models\ClientesRedes;
use App\Models\ClientesResultadosAtendimentos;
use App\Models\ClientesSegmentos;
use App\Models\ClientesTags;
use App\Models\MotivosBloqueios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientesConfiguracoesController extends Controller
{
    private const TIPOS_CAMPO_EXTRA = ['LIVRE', 'NUMERICO', 'LISTA', 'DATA', 'HORA'];

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

    private function propsBase(int|string $empresa): array
    {
        return [
            'empresa_id' => (int) $empresa,
            'tabs' => [
                ['key' => 'campos_extras', 'label' => 'Campos extras', 'icon' => 'bx bxs-quote-alt-left', 'url' => "/{$empresa}/clientes/configuracoes/campos_extras"],
                ['key' => 'tags', 'label' => 'Tags', 'icon' => 'bx bxs-purchase-tag', 'url' => "/{$empresa}/clientes/configuracoes/tags"],
                ['key' => 'segmentos', 'label' => 'Segmentos', 'icon' => 'bx bx-network-chart', 'url' => "/{$empresa}/clientes/configuracoes/segmentos"],
                ['key' => 'redes', 'label' => 'Redes', 'icon' => 'bx bx-sitemap', 'url' => "/{$empresa}/clientes/configuracoes/redes"],
                ['key' => 'excecoes_fiscais', 'label' => 'Excecoes Fiscais', 'icon' => 'bx bxs-bank', 'url' => "/{$empresa}/clientes/configuracoes/excecoes_fiscais"],
                ['key' => 'resultados_atendimentos', 'label' => 'Resultados dos Atendimentos', 'icon' => 'bx bxs-user-check', 'url' => "/{$empresa}/clientes/configuracoes/resultados_atendimentos"],
                ['key' => 'motivos_bloqueio', 'label' => 'Motivos de bloqueio', 'icon' => 'bx bx-block', 'url' => "/{$empresa}/clientes/configuracoes/motivos_bloqueio"],
                ['key' => 'geral', 'label' => 'Geral', 'icon' => 'bx bxs-cog', 'url' => "/{$empresa}/clientes/configuracoes/geral"],
            ],
        ];
    }

    public function camposExtras($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $campos = ClientesCamposExtrasConfiguracoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('tipo')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Clientes/Configuracoes/CamposExtras', [
            ...$this->propsBase($empresa),
            'active_tab' => 'campos_extras',
            'tipos_campos' => self::TIPOS_CAMPO_EXTRA,
            'campos' => $campos,
        ]);
    }

    public function storeCampoExtra(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(self::TIPOS_CAMPO_EXTRA)],
            'obrigatorio' => ['nullable', 'boolean'],
            'opcoes' => ['nullable', 'array'],
            'opcoes.*' => ['nullable', 'string', 'max:120'],
        ]);

        ClientesCamposExtrasConfiguracoes::create([
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'tipo' => $validated['tipo'],
            'obrigatorio' => (bool) ($validated['obrigatorio'] ?? false),
            'opcoes' => $validated['tipo'] === 'LISTA'
                ? collect($validated['opcoes'] ?? [])->filter()->values()->all()
                : null,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Campo extra criado com sucesso.');
    }

    public function updateCampoExtra(Request $request, $empresa, $campo)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = ClientesCamposExtrasConfiguracoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($campo);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(self::TIPOS_CAMPO_EXTRA)],
            'obrigatorio' => ['nullable', 'boolean'],
            'opcoes' => ['nullable', 'array'],
            'opcoes.*' => ['nullable', 'string', 'max:120'],
        ]);

        $model->update([
            'nome' => $validated['nome'],
            'tipo' => $validated['tipo'],
            'obrigatorio' => (bool) ($validated['obrigatorio'] ?? false),
            'opcoes' => $validated['tipo'] === 'LISTA'
                ? collect($validated['opcoes'] ?? [])->filter()->values()->all()
                : null,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Campo extra atualizado com sucesso.');
    }

    public function destroyCampoExtra($empresa, $campo)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = ClientesCamposExtrasConfiguracoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($campo);

        $model->update([
            'excluido' => true,
            'ativo' => false,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Campo extra removido com sucesso.');
    }

    private function listagemSimples($empresa, string $viewKey, string $title, string $placeholder, string $emptyTitle, string $emptyDescription, string $modelClass)
    {
        $this->validarAcessoEmpresa($empresa);

        $items = $modelClass::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Clientes/Configuracoes/ListaSimples', [
            ...$this->propsBase($empresa),
            'active_tab' => $viewKey,
            'list_key' => $viewKey,
            'title' => $title,
            'placeholder' => $placeholder,
            'empty_title' => $emptyTitle,
            'empty_description' => $emptyDescription,
            'items' => $items,
        ]);
    }

    private function storeSimples(Request $request, $empresa, string $modelClass, string $successMessage)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
        ]);

        $ordem = (int) $modelClass::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->max('ordem') + 1;

        $modelClass::create([
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'ordem' => $ordem,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', $successMessage);
    }

    private function updateSimples(Request $request, $empresa, $id, string $modelClass, string $successMessage)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
        ]);

        $model = $modelClass::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($id);

        $model->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', $successMessage);
    }

    private function destroySimples($empresa, $id, string $modelClass, string $successMessage)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = $modelClass::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($id);

        $model->update([
            'excluido' => true,
            'ativo' => false,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', $successMessage);
    }

    public function tags($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'tags',
            'Tags',
            'Criar nova tag de clientes',
            'Nenhuma tag cadastrada',
            'Crie tags para categorizar sua carteira de clientes.',
            ClientesTags::class
        );
    }

    public function storeTag(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, ClientesTags::class, 'Tag criada com sucesso.');
    }

    public function updateTag(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, ClientesTags::class, 'Tag atualizada com sucesso.');
    }

    public function destroyTag($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, ClientesTags::class, 'Tag removida com sucesso.');
    }

    public function segmentos($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'segmentos',
            'Segmentos',
            'Criar novo segmento de cliente',
            'Nenhum segmento cadastrado',
            'Crie segmentos para categorizar sua carteira de clientes.',
            ClientesSegmentos::class
        );
    }

    public function storeSegmento(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, ClientesSegmentos::class, 'Segmento criado com sucesso.');
    }

    public function updateSegmento(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, ClientesSegmentos::class, 'Segmento atualizado com sucesso.');
    }

    public function destroySegmento($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, ClientesSegmentos::class, 'Segmento removido com sucesso.');
    }

    public function redes($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'redes',
            'Redes',
            'Criar nova rede de clientes',
            'Nenhuma rede cadastrada',
            'Crie redes para agrupar clientes da sua carteira.',
            ClientesRedes::class
        );
    }

    public function storeRede(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, ClientesRedes::class, 'Rede criada com sucesso.');
    }

    public function updateRede(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, ClientesRedes::class, 'Rede atualizada com sucesso.');
    }

    public function destroyRede($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, ClientesRedes::class, 'Rede removida com sucesso.');
    }

    public function excecoesFiscais($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'excecoes_fiscais',
            'Excecoes Fiscais',
            'Criar nova excecao fiscal de cliente',
            'Nenhuma excecao fiscal cadastrada',
            'Cadastre as excecoes fiscais utilizadas no cadastro de clientes.',
            ClientesExcecoesFiscais::class
        );
    }

    public function storeExcecaoFiscal(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, ClientesExcecoesFiscais::class, 'Excecao fiscal criada com sucesso.');
    }

    public function updateExcecaoFiscal(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, ClientesExcecoesFiscais::class, 'Excecao fiscal atualizada com sucesso.');
    }

    public function destroyExcecaoFiscal($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, ClientesExcecoesFiscais::class, 'Excecao fiscal removida com sucesso.');
    }

    public function resultadosAtendimentos($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'resultados_atendimentos',
            'Resultados dos Atendimentos',
            'Criar novo resultado de atendimento',
            'Nenhum resultado cadastrado',
            'Crie opcoes de resultados dos atendimentos.',
            ClientesResultadosAtendimentos::class
        );
    }

    public function storeResultadoAtendimento(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, ClientesResultadosAtendimentos::class, 'Resultado criado com sucesso.');
    }

    public function updateResultadoAtendimento(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, ClientesResultadosAtendimentos::class, 'Resultado atualizado com sucesso.');
    }

    public function destroyResultadoAtendimento($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, ClientesResultadosAtendimentos::class, 'Resultado removido com sucesso.');
    }

    public function motivosBloqueio($empresa)
    {
        return $this->listagemSimples(
            $empresa,
            'motivos_bloqueio',
            'Motivos de bloqueio',
            'Criar novo motivo de bloqueio',
            'Nenhum motivo de bloqueio cadastrado',
            'Crie opcoes de motivos de bloqueio para clientes.',
            MotivosBloqueios::class
        );
    }

    public function storeMotivoBloqueio(Request $request, $empresa)
    {
        return $this->storeSimples($request, $empresa, MotivosBloqueios::class, 'Motivo de bloqueio criado com sucesso.');
    }

    public function updateMotivoBloqueio(Request $request, $empresa, $id)
    {
        return $this->updateSimples($request, $empresa, $id, MotivosBloqueios::class, 'Motivo de bloqueio atualizado com sucesso.');
    }

    public function destroyMotivoBloqueio($empresa, $id)
    {
        return $this->destroySimples($empresa, $id, MotivosBloqueios::class, 'Motivo de bloqueio removido com sucesso.');
    }

    public function geral($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $config = ClientesConfiguracoesGerais::query()
            ->firstOrCreate(
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

        return Inertia::render('Clientes/Configuracoes/Geral', [
            ...$this->propsBase($empresa),
            'active_tab' => 'geral',
            'geral' => $config,
        ]);
    }

    public function salvarGeral(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'bloquear_duplicidade_cpf_cnpj' => ['required', 'boolean'],
            'obrigar_cpf_cnpj' => ['required', 'boolean'],
            'obrigar_nome_fantasia' => ['required', 'boolean'],
            'obrigar_telefone' => ['required', 'boolean'],
            'obrigar_email' => ['required', 'boolean'],
            'obrigar_inscricao_estadual' => ['required', 'boolean'],
            'obrigar_info_adicional' => ['required', 'boolean'],
            'obrigar_segmento' => ['required', 'boolean'],
            'obrigar_cep' => ['required', 'boolean'],
            'obrigar_endereco' => ['required', 'boolean'],
            'obrigar_numero' => ['required', 'boolean'],
            'obrigar_complemento' => ['required', 'boolean'],
            'obrigar_bairro' => ['required', 'boolean'],
            'obrigar_cidade' => ['required', 'boolean'],
            'obrigar_estado' => ['required', 'boolean'],
        ]);

        ClientesConfiguracoesGerais::updateOrCreate(
            ['empresa_id' => (int) $empresa],
            $validated
        );

        return back()->with('success', 'Configuracoes gerais salvas com sucesso.');
    }
}

