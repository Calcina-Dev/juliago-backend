<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'producto_id',
        'empresa_id',
        'descripcion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'receta_insumo')
                    ->withPivot('cantidad', 'unidad') // Ej: 100, 'g'
                    ->withTimestamps();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
