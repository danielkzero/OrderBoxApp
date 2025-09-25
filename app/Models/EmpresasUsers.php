<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaUsers extends Model
{
    use HasFactory;

    protected $table = 'empresas_users';

    protected $fillable = [
        'user_id', 'empresa_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Users::class, 'id', 'user_id');
    }
}
