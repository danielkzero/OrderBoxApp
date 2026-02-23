<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosDestaquesItens extends Model
{
    use HasFactory;

    protected $table = 'produtos_destaques_itens';

    protected $fillable = [
        'empresa_id',
        'destaque_id',
        'produto_id',
        'excluido',
    ];

    protected $casts = [
        'excluido' => 'boolean',
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'produto_id', 'id');
    }
}

