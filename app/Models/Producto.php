<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'categoria_id',
        'imagen_url',
        'activo',
        'empresa_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_producto')
                    ->withPivot('precio')
                    ->withTimestamps();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function detallesPedidos()
    {
        return $this->hasMany(PedidoDetalle::class);
    }
}
