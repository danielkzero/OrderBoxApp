<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosPromocoesItens extends Model
{
    use HasFactory;

    protected $table = 'produtos_promocoes_itens';

    protected $fillable = [
        'empresa_id',
        'promocao_id',
        'produto_id',
        'desconto_percentual',
        'excluido',
    ];

    protected $casts = [
        'desconto_percentual' => 'decimal:4',
        'excluido' => 'boolean',
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'produto_id', 'id');
    }
}

