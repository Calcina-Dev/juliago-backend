<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPedido extends Model
{
    protected $table = 'historial_pedido';

    protected $fillable = [
        'pedido_id',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
