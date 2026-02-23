<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesTags extends Model
{
    use HasFactory;

    protected $table = 'clientes_tags';

    protected $fillable = [
        'empresa_id',
        'nome',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];
}

