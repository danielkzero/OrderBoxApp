<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesContatosTelefones extends Model
{
    use HasFactory;

    protected $table = 'clientes_contatos_telefones';

    protected $fillable = [
        'empresa_id', 'cliente_contato_id', 'email', 'tipo'
    ];

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function contato()
    {
        return $this->belongsTo(ClientesContatos::class, 'id', 'cliente_contato_id');
    }
}
