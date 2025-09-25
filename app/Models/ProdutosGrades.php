<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosGrades extends Model
{
    use HasFactory;

    protected $table = 'produtos_grades';

    protected $fillable = [
        'empresa_id', 'produto_id', 'codigo', 'ativo', 'exibir_no_b2b', 'saldo_estoque', 'excluido',
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'id', 'produto_id');
    }

    public function variacoes()
    {
        return $this->hasMany(ProdutosGradesVariacoes::class, 'produto_grade_id');
    }

    public function empresa_id()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
