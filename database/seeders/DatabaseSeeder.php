<?php

namespace Database\Seeders;

use App\Models\Users;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->call([
            EmpresasSeeder::class,
            CategoriasSeeder::class,
            CidadesIbgeSeeder::class,            
            RolesSeeder::class,
            UsersSeeder::class,
            EmpresasUsersSeeder::class,
            Icms_stSeeder::class,
            ClientesSeeder::class,
            ProdutosSeeder::class,
            VariacoesSeeder::class,
            ProdutosImagensSeeder::class,
            TabelasPrecosSeeder::class,
            TabelasPrecosCidadesSeeder::class,
            ProdutosPrecosSeeder::class,
            ProdutosGradesSeeder::class,
            ProdutosGradesVariacoesSeeder::class,
            TiposPedidosSeeder::class,
            CondicoesPagamentosSeeder::class,
            FormasPagamentosSeeder::class,
            PedidosSeeder::class
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
