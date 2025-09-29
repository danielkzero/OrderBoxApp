<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProdutosImagens;
use App\Models\Produtos;

class ProdutosImagensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Produtos::all()->each(function ($produto) {
            // Gera entre 1 e 3 imagens por produto
            ProdutosImagens::factory()->count(rand(1,3))->create([
                'produto_id' => $produto->id,
                'empresa_id' => $produto->empresa_id,
            ]);
        });
    }
}
