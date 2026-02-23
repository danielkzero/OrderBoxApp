<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosPromocoes extends Model
{
    use HasFactory;

    protected $table = 'produtos_promocoes';

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
        return $this->hasMany(ProdutosPromocoesItens::class, 'promocao_id', 'id');
    }
}

