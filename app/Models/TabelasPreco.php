<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelasPreco extends Model
{
     use HasFactory;

    protected $table = 'tabelas_preco';

    protected $fillable = [
        'empresa_id', 'nome', 'tipo', 'acrescimo', 'desconto', 'excluido', 'ultima_alteracao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }
}
