<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresas;
use Faker\Factory as Faker;

class EmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $empresas = Empresas::factory()->create([
            'nome' => 'Hydra Digital',
            'cnpj' => $faker->numerify('##.###.###/####-##'),
            'inscricao_estadual' => $faker->numerify('#########'),
            'excluido' => 0,
        ]);
        // cria 10 empresas "raiz"
        $empresas = Empresas::factory()
            ->count(10)
            ->create();

        // para cada empresa raiz, cria 1 a 3 filiais (que apontam para a raiz)
        $empresas->each(function ($empresa) {
            Empresas::factory()
                ->count(rand(1, 3))
                ->create([
                    'empresa_id' => $empresa->id,
                ]);
        });
    }
}
