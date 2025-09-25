<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produtos extends Model
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
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'id', 'categoria_id');
    }

    public function precos()
    {
        return $this->hasMany(ProdutosPrecos::class, 'produto_id', 'id');
    }

    public function grades()
    {
        return $this->hasMany(ProdutosGrades::class, 'produto_id', 'id');
    }

    public function imagens()
    {
        return $this->hasMany(ProdutosImagens::class, 'produto_id', 'id');
    }
}
