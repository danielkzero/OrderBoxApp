<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoGrade extends Model
{
    use HasFactory;

    protected $table = 'produtos_grades';

    protected $fillable = [
        'empresa_id', 'produto_id', 'codigo', 'ativo', 'exibir_no_b2b', 'saldo_estoque', 'excluido',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id', 'produto_id');
    }

    public function variacoes()
    {
        return $this->hasMany(ProdutoGradeVariacao::class, 'produto_grade_id');
    }

    public function empresa_id()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }
}
