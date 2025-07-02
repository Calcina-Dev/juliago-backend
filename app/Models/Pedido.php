<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'mesa_id',
        'usuario_id',
        'estado',
        'total',
        'empresa_id', // ✅ Agregado
    ];

    protected $appends = ['cancelado'];

    public function getCanceladoAttribute()
    {
        return $this->trashed();
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

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
