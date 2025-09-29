<?php

namespace Database\Seeders;

use App\Models\Users;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $this->call([
            EmpresasSeeder::class,
            RolesSeeder::class,
            UsersSeeder::class,
            Icms_stSeeder::class,
            ClientesSeeder::class,
            ProdutosSeeder::class,
            VariacoesSeeder::class,
            ProdutosImagensSeeder::class,
            TabelasPrecosSeeder::class,
            ProdutosPrecosSeeder::class,
        ]);
        
    }
}
