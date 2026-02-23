<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosStatus extends Model
{
    use HasFactory;

    protected $table = 'pedidos_status';

    protected $fillable = [
        'empresa_id',
        'nome',
        'cor',
        'ordem',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];
}

