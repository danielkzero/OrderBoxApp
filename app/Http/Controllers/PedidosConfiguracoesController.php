<?php

namespace App\Http\Controllers;

use App\Models\PedidosCamposExtrasConfiguracoes;
use App\Models\PedidosConfiguracoesGerais;
use App\Models\PedidosStatus;
use App\Models\TiposPedidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PedidosConfiguracoesController extends Controller
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
                ['key' => 'campos_extras', 'label' => 'Campos extras', 'icon' => 'bx bxs-quote-alt-left', 'url' => "/{$empresa}/pedidos/configuracoes/campos_extras"],
                ['key' => 'status_pedido', 'label' => 'Status de pedido', 'icon' => 'bx bx-shuffle', 'url' => "/{$empresa}/pedidos/configuracoes/status_pedido"],
                ['key' => 'tipo_pedido', 'label' => 'Tipo de pedido', 'icon' => 'bx bxs-file', 'url' => "/{$empresa}/pedidos/configuracoes/tipo_pedido"],
                ['key' => 'geral', 'label' => 'Geral', 'icon' => 'bx bxs-cog', 'url' => "/{$empresa}/pedidos/configuracoes/geral"],
            ],
        ];
    }

    public function camposExtras($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $campos = PedidosCamposExtrasConfiguracoes::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('tipo')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Pedidos/Configuracoes/CamposExtras', [
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

        PedidosCamposExtrasConfiguracoes::create([
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

        $model = PedidosCamposExtrasConfiguracoes::query()
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

        $model = PedidosCamposExtrasConfiguracoes::query()
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

    public function statusPedido($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $status = PedidosStatus::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Pedidos/Configuracoes/StatusPedido', [
            ...$this->propsBase($empresa),
            'active_tab' => 'status_pedido',
            'status_pedido' => $status,
        ]);
    }

    public function storeStatusPedido(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
        ]);

        $ordem = (int) PedidosStatus::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->max('ordem') + 1;

        PedidosStatus::create([
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'ordem' => $ordem,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Status criado com sucesso.');
    }

    public function updateStatusPedido(Request $request, $empresa, $status)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = PedidosStatus::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($status);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
        ]);

        $model->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    public function destroyStatusPedido($empresa, $status)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = PedidosStatus::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($status);

        $model->update([
            'excluido' => true,
            'ativo' => false,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Status removido com sucesso.');
    }

    public function tipoPedido($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $tipos = TiposPedidos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->orderBy('nome')
            ->get();

        return Inertia::render('Pedidos/Configuracoes/TipoPedido', [
            ...$this->propsBase($empresa),
            'active_tab' => 'tipo_pedido',
            'tipos_pedido' => $tipos,
        ]);
    }

    public function storeTipoPedido(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
        ]);

        TiposPedidos::create([
            'empresa_id' => (int) $empresa,
            'nome' => $validated['nome'],
            'excluido' => false,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Tipo de pedido criado com sucesso.');
    }

    public function updateTipoPedido(Request $request, $empresa, $tipo)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = TiposPedidos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($tipo);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
        ]);

        $model->update([
            'nome' => $validated['nome'],
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Tipo de pedido atualizado com sucesso.');
    }

    public function destroyTipoPedido($empresa, $tipo)
    {
        $this->validarAcessoEmpresa($empresa);

        $model = TiposPedidos::query()
            ->where('empresa_id', $empresa)
            ->where('excluido', false)
            ->findOrFail($tipo);

        $model->update([
            'excluido' => true,
            'ultima_alteracao' => now(),
        ]);

        return back()->with('success', 'Tipo de pedido removido com sucesso.');
    }

    public function geral($empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $config = PedidosConfiguracoesGerais::query()
            ->firstOrCreate(
                ['empresa_id' => (int) $empresa],
                [
                    'permitir_itens_duplicados' => false,
                    'nao_permitir_preco_zerado' => false,
                    'obrigar_transportadora' => false,
                    'obrigar_valor_frete' => false,
                ]
            );

        return Inertia::render('Pedidos/Configuracoes/Geral', [
            ...$this->propsBase($empresa),
            'active_tab' => 'geral',
            'geral' => $config,
        ]);
    }

    public function salvarGeral(Request $request, $empresa)
    {
        $this->validarAcessoEmpresa($empresa);

        $validated = $request->validate([
            'permitir_itens_duplicados' => ['required', 'boolean'],
            'nao_permitir_preco_zerado' => ['required', 'boolean'],
            'obrigar_transportadora' => ['required', 'boolean'],
            'obrigar_valor_frete' => ['required', 'boolean'],
        ]);

        PedidosConfiguracoesGerais::updateOrCreate(
            ['empresa_id' => (int) $empresa],
            $validated
        );

        return back()->with('success', 'Configuracoes gerais salvas com sucesso.');
    }
}

