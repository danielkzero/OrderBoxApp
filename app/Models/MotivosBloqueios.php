<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivosBloqueios extends Model
{
    use HasFactory;

    protected $table = 'motivos_bloqueios';

    protected $fillable = [
        'empresa_id',
        'nome',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];
}

