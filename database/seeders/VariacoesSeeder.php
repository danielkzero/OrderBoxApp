<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Variacoes;
use App\Models\VariacoesItens;

class VariacoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Variacoes::factory()->count(10)->create()->each(function ($variacao) {
            // Para cada variação, cria entre 2 e 5 itens
            VariacoesItens::factory()->count(rand(2,5))->create([
                'variacao_id' => $variacao->id,
                'empresa_id' => $variacao->empresa_id,
            ]);
        });
    }
}
