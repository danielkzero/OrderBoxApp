<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icms_st extends Model
{
    use HasFactory;

    protected $table = 'icms_st';

    protected $fillable = [
        'empresa_id', 'codigo_ncm', 'nome_excecao_fiscal', 'estado_destino', 'tipo_st', 'valor_mva', 'valor_pmc',
        'imcs_credito', 'imcs_destino', 'preco_considerado_no_calculo', 'reducao_de_base', 'excluido', 'ultima_alteracao'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
