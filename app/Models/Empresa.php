<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'logo',
        'moneda',
        'modo_mantenimiento',
        'activa', // ✅ nuevo campo
    ];

    // Relaciones comunes
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
