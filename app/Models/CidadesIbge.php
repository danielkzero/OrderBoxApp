<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CidadesIbge extends Model
{
    use HasFactory;

    protected $table = 'cidades_ibge';

    protected $fillable = [
        'uf_codigo', 'uf_nome', 'meso_codigo', 'meso_nome', 'micro_codigo', 'micro_nome',
        'municipio_codigo', 'municipio_nome', 'pop_urbana', 'pop_urbana_central', 'pop_rural',
        'area_km2', 'pop_km2', 'lat', 'lon', 'pop_total', 'svg', 'path'
    ];
}
