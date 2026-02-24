<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosConfiguracoesGerais extends Model
{
    use HasFactory;

    protected $table = 'produtos_configuracoes_gerais';

    protected $fillable = [
        'empresa_id',
        'inativos_recentes_dias',
        'inativos_antigos_dias',
        'gerenciar_estoque',
        'ultima_alteracao',
    ];

    protected $casts = [
        'gerenciar_estoque' => 'boolean',
    ];
}
