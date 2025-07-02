<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreCaja extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'monto_total',
        'inicio_turno',
        'fin_turno',
        'estado',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
