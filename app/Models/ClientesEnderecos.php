<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesEnderecos extends Model
{
    use HasFactory;

    protected $table = 'clientes_enderecos';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'rua', 'complemento', 'bairro', 'cep', 'municipio_codigo', 'ultima_alteracao'
    ];

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id', 'cliente_id');
    }

    public function ibge()
    {
        return $this->belongsTo(CidadesIbge::class, 'municipio_codigo', 'municipio_codigo');
    }
}
