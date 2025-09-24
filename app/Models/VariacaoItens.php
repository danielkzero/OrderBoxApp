<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariacaoItens extends Model
{
    use HasFactory;

    protected $table = 'variacao_itens';

    protected $fillable = ['empresa_id', 'variacao_id', 'nome', 'cor', 'imagem', 'excluido' ];

    public function empresa()
    {
        return this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }

    public function variacao_itens()
    {
        return this->hasMany(VaricaoItens::class, 'id', 'variacao_id');
    }
}
