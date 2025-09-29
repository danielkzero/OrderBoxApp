<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ClientesContatos;
use App\Models\Empresas;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesContatosEmails>
 */
class ClientesContatosEmailsFactory extends Factory
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
            'cliente_contato_id' => ClientesContatos::inRandomOrder()->first()->id ?? ClientesContatos::factory()->create()->id,
            'email' => $this->faker->safeEmail(),
            'tipo' => $this->faker->randomElement(['M', 'F', 'C']), 
        ];
    }
}
