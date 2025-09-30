<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedidos>
 */
class PedidosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id,
            'cliente_id' => \App\Models\Clientes::inRandomOrder()->first()->id,
            'forma_pagamento_id' => \App\Models\FormasPagamentos::inRandomOrder()->first()->id ?? null,
            'condicao_pagamento_id' => \App\Models\CondicoesPagamentos::inRandomOrder()->first()->id ?? null,
            'transportadora_id' => null,
            'nome_contato' => $this->faker->name,
            'status' => $this->faker->randomElement(['pendente','aprovado','cancelado']),
            'status_b2b' => $this->faker->randomElement(['pendente','aprovado','cancelado']),
            'status_faturamento' => $this->faker->randomElement(['pendente','faturado','cancelado']),
            'cupom_de_desconto' => $this->faker->optional()->bothify('????-####'),
            'valor_frete' => $this->faker->optional()->randomFloat(2, 10, 200),
            'total' => $this->faker->randomFloat(2, 100, 5000),
            'transportadora_nome' => $this->faker->company,
            'data_emissao' => $this->faker->date(),
            'data_criacao' => now(),
            'ultima_alteracao' => now(),
            'criador_id' => \App\Models\Users::inRandomOrder()->first()->id ?? null,
            'observacoes' => $this->faker->optional()->paragraph,
            'tipo_pedido_id' => \App\Models\TiposPedidos::inRandomOrder()->first()->id ?? null,
        ];
    }
}
