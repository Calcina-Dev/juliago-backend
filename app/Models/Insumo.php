<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'unidad',
        'stock',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }
}
