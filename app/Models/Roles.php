<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [ 
        'empresa_id', 'nome', 'descricao', 'permissoes' 
    ];

    protected $casts = [ 'permissoes' => 'array' ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id', 'empresa_id');
    }
}
