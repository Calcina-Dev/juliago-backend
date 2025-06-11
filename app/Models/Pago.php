<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'monto',
        'metodo_pago',
        'pagado_en',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
