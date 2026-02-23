<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesEnderecos extends Model
{
    use HasFactory;

    protected $table = 'clientes_enderecos';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'rua', 'numero', 'complemento', 'bairro', 'cep', 'municipio_codigo', 'ultima_alteracao'
    ];

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id', 'id');
    }

    public function ibge()
    {
        return $this->belongsTo(CidadesIbge::class, 'municipio_codigo', 'municipio_codigo');
    }
}
