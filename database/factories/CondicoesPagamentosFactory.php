<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CondicoesPagamentos>
 */
class CondicoesPagamentosFactory extends Factory
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
             'nome' => function() {
                // Define o incremento base (10, 20, etc)
                $increment = rand(10, 30);

                // Quantidade de elementos da sequência (aleatório entre 2 e 5)
                $count = rand(2, 5);

                // Número inicial da sequência (múltiplo do incremento)
                $start = $increment * rand(1, 5);

                // Monta a sequência
                $sequence = [];
                for ($i = 0; $i < $count; $i++) {
                    $sequence[] = $start + ($i * $increment);
                }

                // Transforma em string separada por "/"
                return implode('/', $sequence);
            },
            'valor_minimo' => $this->faker->randomFloat(2,600,5000),
            'excluido' => $this->faker->boolean(),
            'ultima_alteracao' => now()
        ];
    }
}
