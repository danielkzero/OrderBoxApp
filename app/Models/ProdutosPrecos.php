<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosPrecos extends Model
{
    use HasFactory;

    protected $table = 'produtos_precos';

    protected $fillable = [
        'empresa_id', 'tabela_id', 'produto_id', 'preco', 'excluido', 'ultima_alteracao',
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'id', 'produto_id');
    }

    public function tabelas()
    {
        return $this->belongsTo(TabelasPrecos::class, 'tabela_id', 'id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
