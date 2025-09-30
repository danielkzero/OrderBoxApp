<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CidadesIbgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path('seeders/data/ibge_relacao.csv');

        if (!file_exists($file)) {
            $this->command->error("Arquivo CSV não encontrado em: $file");
            return;
        }

        if (($handle = fopen($file, 'r')) !== false) {
            // Pega cabeçalho e remove aspas
            $header = fgetcsv($handle, 0, ';');

            // Limpa BOM, aspas e espaços
            $header = array_map(function ($h) {
                // Remove BOM (UTF-8), aspas e espaços extras
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                return trim($h, '"\' ');
            }, $header);
            
            $count = 0;
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $data = array_combine($header, $row);

                // Normaliza números decimais (troca vírgula por ponto)
                $areaKm2 = isset($data['AREA_KM2']) ? str_replace(',', '.', $data['AREA_KM2']) : null;
                $popKm2 = isset($data['POP_KM2']) ? str_replace(',', '.', $data['POP_KM2']) : null;

                DB::table('cidades_ibge')->updateOrInsert(
                    ['municipio_codigo' => $data['IBGE_CI']],  // evita duplicados
                    [
                        'uf_codigo' => isset($data['IBGE_UF']) ? str_pad($data['IBGE_UF'], 2, '0', STR_PAD_LEFT) : null,
                        'uf_nome' => $data['NOME_UF'] ?? null,
                        'meso_codigo' => $data['IBGE_MS'] ?? null,
                        'meso_nome' => $data['NOME_MS'] ?? null,
                        'micro_codigo' => $data['IBGE_MC'] ?? null,
                        'micro_nome' => $data['NOME_MC'] ?? null,
                        'municipio_nome' => $data['NOME_CI'] ?? null,
                        'pop_urbana' => $data['POP_URBANA'] ?? null,
                        'pop_urbana_central' => $data['POP_URBANA_CENTRAL'] ?? null,
                        'pop_rural' => $data['POP_RURAL'] ?? null,
                        'area_km2' => $areaKm2,
                        'pop_km2' => $popKm2,
                        'lat' => trim($data['LAT'] ?? null),  // remove quebras de linha
                        'lon' => trim($data['LON'] ?? null),
                        'pop_total' => $data['POP_TOTAL'] ?? null,
                        'svg' => $data['SVG'] ?? null,
                        'path' => $data['PATH'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $count++;
            }

            fclose($handle);
            $this->command->info("Importadas/atualizadas {$count} cidades IBGE.");
        }
    }
}
