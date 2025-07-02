<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcuenta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pedido_id',
        'usuario_id',    // cliente que pagará esta subcuenta
        'empresa_id',
        'total',
        'estado',        // pendiente, pagado, etc.
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function detalles()
    {
        return $this->hasMany(SubcuentaDetalle::class);
    }
}
