<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoPreco extends Model
{
    use HasFactory;

    protected $table = 'produto_precos';

    protected $fillable = [
        'empresa_id', 'tabela_id', 'produto_id', 'preco', 'excluido', 'ultima_alteracao',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id', 'produto_id');
    }

    public function tabela()
    {
        return $this->belongsTo(TabelaPreco::class, 'id', 'tabela_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }
}
