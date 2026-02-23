<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosDestaques extends Model
{
    use HasFactory;

    protected $table = 'produtos_destaques';

    protected $fillable = [
        'empresa_id',
        'nome',
        'data_inicio',
        'data_fim',
        'ativo',
        'excluido',
        'ultima_alteracao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
        'excluido' => 'boolean',
    ];

    public function itens()
    {
        return $this->hasMany(ProdutosDestaquesItens::class, 'destaque_id', 'id');
    }
}

