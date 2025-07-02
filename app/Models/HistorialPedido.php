<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistorialPedido extends Model
{
    use SoftDeletes;

    protected $table = 'historial_pedidos'; // ✅ nombre plural correcto

    protected $fillable = [
        'pedido_id',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
        'empresa_id', // ✅ nuevo
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
}
