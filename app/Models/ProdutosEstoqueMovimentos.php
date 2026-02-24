<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosEstoqueMovimentos extends Model
{
    use HasFactory;

    protected $table = 'produtos_estoque_movimentos';

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'user_id',
        'tipo',
        'quantidade',
        'saldo_anterior',
        'saldo_atual',
        'observacoes',
        'origem',
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'produto_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}
