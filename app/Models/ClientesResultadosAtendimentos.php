<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesResultadosAtendimentos extends Model
{
    use HasFactory;

    protected $table = 'clientes_resultados_atendimentos';

    protected $fillable = [
        'empresa_id',
        'nome',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];
}

