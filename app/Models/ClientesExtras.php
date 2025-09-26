<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesExtras extends Model
{
    use HasFactory;

    protected $table = 'clientes_extras';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'campo_extra_id', 'nome', 'tipo', 'valor_texto', 'valor_data', 'valor_decimal', 'nome_arquivo', 'valor_arquivo'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id', 'cliente_id');
    }
}
