<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PedidosExtrasFactory extends Factory
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
            'pedido_id' => \App\Models\Pedidos::inRandomOrder()->first()->id,
            'nome' => $this->faker->word,
            'tipo' => $this->faker->randomElement(['texto', 'data', 'decimal', 'hora', 'lista']),
            'valor_texto' => $this->faker->optional()->sentence,
            'valor_data' => $this->faker->optional()->date(),
            'valor_decimal' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'valor_hora' => $this->faker->optional()->randomFloat(2, 0, 24),
            'valor_lista' => $this->faker->optional()->randomElement([
                json_encode(['op1', 'op2', 'op3']),
                json_encode(['item1', 'item2'])
            ]),
        ];
    }
}
