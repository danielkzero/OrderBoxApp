<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variacoes extends Model
{
    use HasFactory;

    protected $table = 'variacoes';

    protected $fillable = [ 
        'empresa_id', 'nome', 'ordem', 'excluido' 
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function variacao_itens()
    {
        return $this->hasMany(VariacoesItens::Class, 'variacao_id', 'id');
    }
}
