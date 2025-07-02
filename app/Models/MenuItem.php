<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'rol',
        'label',
        'icon',
        'route',
        'orden',
        'visible',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
