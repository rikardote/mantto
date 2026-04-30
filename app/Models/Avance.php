<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avance extends Model
{
    protected $fillable = ['solicitud_id', 'user_id', 'comentario', 'porcentaje', 'fecha'];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function solicitud() { return $this->belongsTo(SolicitudMantenimiento::class, 'solicitud_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
