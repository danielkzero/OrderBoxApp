<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesTelefones extends Model
{
    use HasFactory;

    protected $table = 'clientes_telefones';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'numero', 'tipo', 'ultima_alteracao'
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
}
