<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocion extends Model
{
    use SoftDeletes;

   protected $table = 'promociones';



    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',          // Ej: 'porcentaje', 'fijo', '2x1'
        'valor',         // Ej: 10 (%) o 5 (soles)
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'promocion_producto')
                    ->withTimestamps();
    }
}
