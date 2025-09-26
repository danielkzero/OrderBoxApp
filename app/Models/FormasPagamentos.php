<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormasPagamentos extends Model
{
    use HasFactory;

    protected $table = 'formas_pagamentos';

    protected $fillable = [
        'empresa_id', 'nome', 'excluido', 'ultima_alteracao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
