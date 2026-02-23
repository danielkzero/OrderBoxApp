<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesCamposExtrasConfiguracoes extends Model
{
    use HasFactory;

    protected $table = 'clientes_campos_extras_configuracoes';

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
        'obrigatorio' => 'boolean',
        'ativo' => 'boolean',
        'excluido' => 'boolean',
        'opcoes' => 'array',
    ];
}

