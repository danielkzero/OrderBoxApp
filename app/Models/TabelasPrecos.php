<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelasPrecos extends Model
{
     use HasFactory;

    protected $table = 'tabelas_precos';

    protected $fillable = [
        'empresa_id', 'nome', 'tipo', 'acrescimo', 'desconto', 'excluido', 'ultima_alteracao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function produtos()
    {
        return $this->hasMany(ProdutosPrecos::class, 'tabela_id', 'id');
    }

    public function cidades()
    {
        return $this->hasMany(CidadesIbge::class, 'tabela_preco_id', 'id');
    }
}
