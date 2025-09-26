<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiposPedidos extends Model
{
    use HasFactory;

    protected $table = 'tipos_pedidos';

    protected $fillable = [
        'nome', 'excluido', 'ultima_alteracao', 'empresa_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
