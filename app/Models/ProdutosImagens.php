<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosImagens extends Model
{
    use HasFactory;

    protected $table = 'produto_imagens';

    protected $fillable = [
        'empresa_id', 'produto_id', 'imagem_base64', 'ordem'
    ];

    public function produto()
    {
        return $this->belongsTo(Produtos::class, 'id', 'produto_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id', 'empresa_id');
    }
}
