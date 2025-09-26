<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CondicoesPagamentos extends Model
{
    use HasFactory;

    protected $table = 'condicoes_pagamentos';

    protected $fillable = [
        'empresa_id', 'nome', 'valor_minimo', 'excluido', 'ultima_alteracao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
