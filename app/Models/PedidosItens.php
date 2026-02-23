<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosItens extends Model
{
    use HasFactory;

    protected $table = 'pedidos_itens';

    protected $fillable = [
        'empresa_id', 'pedido_id', 'produto_id', 'tabela_preco_id', 'preco_tabela', 'preco_liquido', 'ipi', 'st', 'subtotal', 'quantidade', 
        'quantidade_grades', 'observacoes', 'descontos_do_vendedor', 'descontos_de_promocoes', 'descontos_de_politicas', 'desconto_de_cupom', 'excluido'
    ];

    protected $casts = [
        'descontos_do_vendedor' => 'array',
        'descontos_de_promocoes' => 'array',
        'descontos_de_politicas' => 'array',
        'quantidade_grades' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'empresa_id', 'id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedidos::class, 'pedido_id', 'id');
    }

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'produto_id', 'id');
    }

    public function tabela_preco()
    {
        return $this->belongsTo(TabelasPrecos::class, 'tabela_preco_id', 'id');
    }
}
