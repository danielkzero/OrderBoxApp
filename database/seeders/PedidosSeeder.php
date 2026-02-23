<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PedidosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pedidos::factory()
            ->count(3000) // cria 3000 pedidos
            ->create()
            ->each(function ($pedido) {
                // Itens do pedido
                $pedido->itens()->createMany(
                    \App\Models\PedidosItens::factory()
                        ->count(rand(1, 5))
                        ->make([
                            'empresa_id' => $pedido->empresa_id,
                        ])
                        ->toArray()
                );

                // Extras do pedido
                $pedido->extras()->createMany(
                    \App\Models\PedidosExtras::factory()
                        ->count(rand(0, 3))
                        ->make([
                            'empresa_id' => $pedido->empresa_id,
                        ])
                        ->toArray()
                );
            });
    }
}
