<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelasPrecosCidades extends Model
{
    use HasFactory;

    protected $table = 'tabelas_precos_cidades';

    protected $fillable = [
        'empresa_id', 'tabela_preco_id', 'municipio_codigo'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }

    public function tabela()
    {
        return $this->belongsTo(TabelasPrecos::class, 'id', 'tabela_preco_id');
    }

    public function municipio()
    {
        return $this->belongsTo(CidadesIbge::class, 'municipio_codigo', 'municipio_codigo');
    }
}
