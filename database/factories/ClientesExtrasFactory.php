<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ClientesExtras;
use App\Models\Clientes;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesExtras>
 */
class ClientesExtrasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['texto', 'data', 'decimal', 'arquivo']); // tipos possíveis
        $valorTexto = null;
        $valorData = null;
        $valorDecimal = null;
        $nomeArquivo = null;
        $valorArquivo = null;

        // Define o valor dependendo do tipo
        switch ($tipo) {
            case 'texto':
                $valorTexto = $this->faker->sentence();
                break;
            case 'data':
                $valorData = $this->faker->date();
                break;
            case 'decimal':
                $valorDecimal = $this->faker->randomFloat(2, 0, 10000);
                break;
            case 'arquivo':
                $nomeArquivo = $this->faker->word() . '.pdf';
                $valorArquivo = '/uploads/' . $nomeArquivo;
                break;
        }

        return [
            'empresa_id' => Empresas::factory(),
            'cliente_id' => Clientes::factory(),
            'campo_extra_id' => $this->faker->numberBetween(1, 50),
            'nome' => $this->faker->word(),
            'tipo' => $tipo,
            'valor_texto' => $valorTexto,
            'valor_data' => $valorData,
            'valor_decimal' => $valorDecimal,
            'nome_arquivo' => $nomeArquivo,
            'valor_arquivo' => $valorArquivo,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
