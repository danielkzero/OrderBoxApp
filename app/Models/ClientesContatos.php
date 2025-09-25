<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesContatos extends Model
{
    use HasFactory;

    protected $table = 'clientes_contatos';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'nome', 'cargo', 'excluido'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id', 'cliente_id');
    }

    public function emails()
    {
        return $this->hasMany(ClientesContatosEmails::class, 'cliente_contato_id', 'id');
    }

    public function telefones()
    {
        return $this->hasMany(ClientesContatosTelefones::class, 'cliente_contato_id', 'id');
    }

}
