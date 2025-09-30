<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Clientes;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesEnderecos>
 */
class ClientesEnderecosFactory extends Factory
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
            'rua' => $this->faker->streetName,
            'numero' => $this->faker->buildingNumber,
            'complemento' => $this->faker->secondaryAddress,
            'bairro' => $this->faker->citySuffix,
            'cep' => $this->faker->postcode,
            'municipio_codigo' => \App\Models\CidadesIbge::inRandomOrder()->first()->municipio_codigo ?? \App\Models\CidadesIbge::factory()->create()->municipio_codigo, 
            'ultima_alteracao' => now(),
        ];
    }
}
