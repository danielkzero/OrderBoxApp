<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clientes;
use App\Models\Empresas;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesContatos>
 */
class ClientesContatosFactory extends Factory
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
            'cliente_id' => Clientes::inRandomOrder()->first()->id ?? Clientes::factory()->create()->id,
            'nome' => $this->faker->name(),
            'cargo' => $this->faker->jobTitle(),
        ];
    }
}
