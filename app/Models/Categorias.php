<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [ 'empresa_id', 'nome', 'categoria_pai_id', 'excluido', 'ultima_alteracao' ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }

    public function categoriaPai()
    {
        return $this->belongsTo(Categoria::class, 'id', 'categoria_pai_id');
    }

    public function categoriasFilhas()
    {
        return $this->hasMany(Categoria::class, 'categoria_pai_id', 'id');
    }
}
