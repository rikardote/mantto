<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMantenimiento extends Model
{
    protected $table = 'tipos_mantenimiento';
    protected $fillable = ['nombre', 'activo'];
}
