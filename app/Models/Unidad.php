<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';
    protected $fillable = ['nombre', 'tipo', 'activo'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(SolicitudMantenimiento::class);
    }
}
