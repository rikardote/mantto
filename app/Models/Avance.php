<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avance extends Model
{
    use HasFactory;
    protected $fillable = ['solicitud_id', 'user_id', 'comentario', 'porcentaje', 'fecha', 'archivo_path'];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function solicitud() { return $this->belongsTo(SolicitudMantenimiento::class, 'solicitud_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
