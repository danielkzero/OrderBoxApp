<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosCamposExtrasConfiguracoes extends Model
{
    use HasFactory;

    protected $table = 'pedidos_campos_extras_configuracoes';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'obrigatorio',
        'opcoes',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];

    protected $casts = [
        'opcoes' => 'array',
        'obrigatorio' => 'boolean',
        'ativo' => 'boolean',
        'excluido' => 'boolean',
    ];
}

