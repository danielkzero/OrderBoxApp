<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'empresa_id', 'icms_st_id', 'tipo', 'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual', 'suframa',
        'rua', 'numero', 'complemento', 'bairro', 'cep', 'municipio_codigo', 'tag_id', 'segmento_id', 'rede_id', 'excecao_fiscal_id',
        'bloqueado', 'motivo_bloqueio_id', 'observacao',
        'ultima_alteracao', 'excluido'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id', 'id');
    }

    public function icms_st()
    {
        return $this->belongsTo(Icms_st::class, 'icms_st_id', 'id');
    }

    public function motivo_bloqueio()
    {
        return $this->belongsTo(MotivosBloqueios::class, 'motivo_bloqueio_id', 'id');
    }

    public function contatos()
    {
        return $this->hasMany(ClientesContatos::class, 'cliente_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany(ClientesEmails::class, 'cliente_id', 'id');
    }

    public function enderecos()
    {
        return $this->hasMany(ClientesEnderecos::class, 'cliente_id', 'id');
    }

    public function telefones()
    {
        return $this->hasMany(ClientesTelefones::class, 'cliente_id', 'id');
    }

    public function campos_extras()
    {
        return $this->hasMany(ClientesExtras::class, 'cliente_id', 'id');
    }

    public function tag()
    {
        return $this->belongsTo(ClientesTags::class, 'tag_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            ClientesTags::class,
            'clientes_tags_clientes',
            'cliente_id',
            'tag_id'
        )->withTimestamps();
    }

    public function segmento()
    {
        return $this->belongsTo(ClientesSegmentos::class, 'segmento_id', 'id');
    }

    public function rede()
    {
        return $this->belongsTo(ClientesRedes::class, 'rede_id', 'id');
    }

    public function excecao_fiscal()
    {
        return $this->belongsTo(ClientesExcecoesFiscais::class, 'excecao_fiscal_id', 'id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class, 'cliente_id', 'id');
    }

    public function categorias_permissoes()
    {
        return $this->belongsToMany(
            Categorias::class,
            'clientes_categorias_permissoes',
            'cliente_id',
            'categoria_id'
        )->withTimestamps();
    }

    public function condicoes_pagamentos_permissoes()
    {
        return $this->belongsToMany(
            CondicoesPagamentos::class,
            'clientes_condicoes_pagamentos_permissoes',
            'cliente_id',
            'condicao_pagamento_id'
        )->withTimestamps();
    }

    public function tabelas_precos_permissoes()
    {
        return $this->belongsToMany(
            TabelasPrecos::class,
            'clientes_tabelas_precos_permissoes',
            'cliente_id',
            'tabela_preco_id'
        )->withTimestamps();
    }

    public function ibge()
    {
        return $this->belongsTo(CidadesIbge::class, 'municipio_codigo', 'municipio_codigo');
    }
}
