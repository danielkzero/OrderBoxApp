<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariacoesItens extends Model
{
    use HasFactory;

    protected $table = 'variacoes_itens';

    protected $fillable = [
        'empresa_id', 'variacao_id', 'nome', 'cor', 'imagem', 'excluido'
    ];

    public function empresa()
    {
        return this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function variacoes_itens()
    {
        return this->hasMany(VaricoesItens::class, 'id', 'variacao_id');
    }
}
