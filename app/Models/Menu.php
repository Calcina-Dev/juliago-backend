<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = ['nombre', 'es_actual', 'activo', 'empresa_id'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'menu_producto')
                    ->withPivot('precio')
                    ->withTimestamps();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
