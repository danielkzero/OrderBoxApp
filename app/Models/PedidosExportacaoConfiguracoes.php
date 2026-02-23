<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosExportacaoConfiguracoes extends Model
{
    use HasFactory;

    protected $table = 'pedidos_exportacao_configuracoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'configuracoes',
    ];

    protected $casts = [
        'configuracoes' => 'array',
    ];
}

