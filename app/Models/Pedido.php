<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['mesa_id', 'estado', 'total'];
    protected $appends = ['cancelado'];

    public function getCanceladoAttribute()
    {
        return $this->trashed(); // Devuelve true si el pedido fue eliminado (soft delete)
    }


    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }
    public function historial()
    {
        return $this->hasMany(HistorialPedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }


}
