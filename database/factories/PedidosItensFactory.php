<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PedidosItens>
 */
class PedidosItensFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantidade = $this->faker->numberBetween(1, 10);
        $preco = $this->faker->randomFloat(2, 10, 500);

        return [
            'empresa_id' => \App\Models\Empresas::inRandomOrder()->first()->id,
            'pedido_id' => \App\Models\Pedidos::inRandomOrder()->first()->id,
            'produto_id' => \App\Models\Produtos::inRandomOrder()->first()->id,
            'tabela_preco_id' => \App\Models\TabelasPrecos::inRandomOrder()->first()->id ?? null,
            'preco_tabela' => $preco,
            'preco_liquido' => $preco * (1 - $this->faker->randomFloat(2, 0, 0.2)),  // aplica até 20% desconto
            'ipi' => $this->faker->randomFloat(2, 0, 50),
            'st' => $this->faker->randomFloat(2, 0, 50),
            'subtotal' => $quantidade * $preco,
            'quantidade' => $quantidade,
            'quantidade_grades' => $this->faker->optional()->randomElement([
                json_encode([2, 7]),
                json_encode([1, 3, 5])
            ]),
            'observacoes' => $this->faker->optional()->sentence,
            'descontos_do_vendedor' => $this->faker->optional()->randomElement([
                json_encode([5, 10]),
                json_encode([2, 7, 15]),
                json_encode([0])
            ]),
            'descontos_de_promocoes' => $this->faker->optional()->randomElement([
                json_encode([5, 15]),
                json_encode([10, 20])
            ]),
            'descontos_de_politicas' => $this->faker->optional()->randomElement([
                json_encode([2, 7]),
                json_encode([1, 3, 5])
            ]),
            'desconto_de_cupom' => $this->faker->optional()->randomFloat(2, 0, 50),
            'excluido' => $this->faker->boolean(10),  // 10% chance de ser excluído
        ];
    }
}
