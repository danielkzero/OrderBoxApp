<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesContatosTelefones extends Model
{
    use HasFactory;

    protected $table = 'clientes_contatos_telefones';

    protected $fillable = [
        'empresa_id', 'cliente_contato_id', 'numero', 'tipo'
    ];

    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id', 'id');
    }

    public function contato()
    {
        return $this->belongsTo(ClientesContatos::class, 'cliente_contato_id', 'id');
    }
}
