<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoGradeVariacao extends Model
{
    use HasFactory;

    protected $table = 'produtos_grade_variacoes';

    protected $fillable = [
        'empresa_id', 'produto_grade_id', 'nome', 'valor',
    ];

    public function grade()
    {
        return $this->belongsTo(ProdutoGrade::class, 'id', 'produto_grade_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }
}
