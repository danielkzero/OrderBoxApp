<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosConfiguracoesGerais extends Model
{
    use HasFactory;

    protected $table = 'pedidos_configuracoes_gerais';

    protected $fillable = [
        'empresa_id',
        'permitir_itens_duplicados',
        'nao_permitir_preco_zerado',
        'obrigar_transportadora',
        'obrigar_valor_frete',
    ];

    protected $casts = [
        'permitir_itens_duplicados' => 'boolean',
        'nao_permitir_preco_zerado' => 'boolean',
        'obrigar_transportadora' => 'boolean',
        'obrigar_valor_frete' => 'boolean',
    ];
}

