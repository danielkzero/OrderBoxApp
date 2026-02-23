<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesExcecoesFiscais extends Model
{
    use HasFactory;

    protected $table = 'clientes_excecoes_fiscais';

    protected $fillable = [
        'empresa_id',
        'nome',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];
}

