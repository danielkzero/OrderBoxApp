<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [ 
        'empresa_id', 'categoria_id', 'codigo', 'nome', 'observacoes', 'preco_tabela',
        'preco_minimo', 'ipi', 'tipo_ipi', 'comissao', 'saldo_estoque', 'st', 'ativo', 'excluido', 'moeda',
        'codigo_ncm', 'multiplo', 'peso_bruto', 'largura', 'altura', 'comprimento', 'peso_dimensoes_unitario',
        'exibir_no_b2b', 'unidade', 'ultima_alteracao',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id', 'categoria_id');
    }

    public function precos()
    {
        return $this->hasMany(ProdutoPreco::class, 'produto_id', 'id');
    }

    public function grades()
    {
        return $this->hasMany(ProdutoGrade::class, 'produto_id', 'id');
    }
}
