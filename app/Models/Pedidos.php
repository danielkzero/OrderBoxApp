<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'empresa_id', 'cliente_id', 'forma_pagamento_id', 'condicao_pagamento_id', 'transportadora_id', 'nome_contato', 'status', 'status_b2b',
        'status_faturamento', 'cupom_de_desconto', 'valor_frete', 'total', 'transportadora_nome', 'data_emissao', 'data_criacao', 'ultima_alteracao',
        'criador_id', 'observacoes', 'tipo_pedido_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function itens()
    {
        return $this->hasMany(PedidosItens::class, 'pedido_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'id', 'cliente_id');
    }

    public function forma_pagamento()
    {
        return $this->belongsTo(FormasPagamentos::class, 'id', 'forma_pagamento_id');
    }

    public function condicao_pagamento()
    {
        return $this->belongsTo(CondicoesPagamentos::class, 'id', 'condicao_pagamento_id');
    }

    public function extras()
    {
        return $this->hasMany(PedidosExtras::class, 'pedido_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Users::class, 'id', 'criador_id');
    }

    public function tipo_pedido()
    {
        return $this->belongsTo(TiposPedidos::class, 'id', 'tipo_pedido_id');
    }
}

