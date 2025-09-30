<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosGradesVariacoes extends Model
{
    use HasFactory;

    protected $table = 'produtos_grades_variacoes';

    protected $fillable = [
        'empresa_id', 'produto_grade_id', 'nome', 'valor',
    ];

    public function grade()
    {
        return $this->belongsTo(ProdutosGrades::class, 'id', 'produto_grade_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
