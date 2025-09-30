<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosExtras extends Model
{
    use HasFactory;

    protected $table = 'pedidos_extras';

    protected $fillable = [
        'empresa_id', 'pedido_id', 'nome', 'tipo', 'valor_texto', 'valor_data', 'valor_decimal', 'valor_hora', 'valor_lista'
    ];

}
