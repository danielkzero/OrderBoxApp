<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'empresa_id', 'icms_st_id', 'tipo', 'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual', 'suframa',
        'rua', 'numero', 'complemento', 'bairro', 'cep', 'municipio_codigo', 'bloqueado', 'motivo_bloqueio_id', 'observacao',
        'ultima_alteracao', 'excluido'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function icms_st()
    {
        return $this->belongsTo(Icms_st::class, 'id', 'icms_st_id');
    }

    public function motivo_bloqueio()
    {
        return $this->belongsTo(MotivosBloqueios::class, 'id', 'motivo_bloqueio_id');
    }

    public function contatos()
    {
        return $this->hasMany(ClientesContatos::class, 'cliente_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany(ClientesEmails::class, 'cliente_id', 'id');
    }

    public function enderecos()
    {
        return $this->hasMany(ClientesEnderecos::class, 'cliente_id', 'id');
    }

    public function telefones()
    {
        return $this->hasMany(ClientesTelefones::class, 'cliente_id', 'id');
    }

    public function campos_extras()
    {
        return $this->hasMany(ClientesExtras::class, 'cliente_id', 'id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class, 'cliente_id', 'id');
    }
}
