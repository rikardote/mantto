<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMantenimiento extends Model
{
    use HasFactory;
    protected $table = 'tipos_mantenimiento';
    protected $fillable = ['nombre', 'activo'];
}
