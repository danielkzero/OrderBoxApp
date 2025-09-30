<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categorias;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categorias::factory()
            ->count(20)
            ->create();

        $categoria->each(function ($categoria) {
            Categorias::factory()
                ->count(rand(1, 3))
                ->create([
                    'categoria_pai_id' => $categoria->id,
                ]);
        });
    }
}
