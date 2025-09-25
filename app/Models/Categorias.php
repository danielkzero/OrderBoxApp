<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [ 
        'empresa_id', 'nome', 'categoria_pai_id', 'excluido', 'ultima_alteracao' 
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function categoriaPai()
    {
        return $this->belongsTo(Categorias::class, 'id', 'categoria_pai_id');
    }

    public function categoriasFilhas()
    {
        return $this->hasMany(Categorias::class, 'categoria_pai_id', 'id');
    }
}
