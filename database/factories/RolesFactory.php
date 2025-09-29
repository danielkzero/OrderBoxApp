<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Roles>
 */
class RolesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresas::inRandomOrder()->first()->id ?? Empresas::factory()->create()->id,
            'nome' => $this->faker->name(),
            'descricao' => $this->faker->sentence,
            'permissoes' => [
                'clientes' => $this->faker->randomElement(['c', 'r', 'u', 'd', 'crud']),
                'pedidos' => $this->faker->randomElement(['c', 'r', 'u', 'd', 'crud']),
            ],
        ];
    }
}
