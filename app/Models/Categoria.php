<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'destino',      // cocina / bar (si ya lo usas en migraciones)
        'empresa_id',   // ✅ añadido
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
