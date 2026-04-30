<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudMantenimiento extends Model
{
    protected $table = 'solicitudes_mantenimiento';
    protected $fillable = [
        'unidad_id', 'servicio_id', 'tipo_mantenimiento_id', 'prioridad_id',
        'titulo', 'descripcion', 'descripcion_servicio_otro',
        'folio_oficio', 'orden_servicio', 'estatus',
        'fecha_solicitud', 'fecha_atencion', 'fecha_cierre', 'fecha_limite',
        'creado_por'
    ];

    public function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_atencion' => 'datetime',
            'fecha_cierre' => 'datetime',
            'fecha_limite' => 'datetime',
        ];
    }

    public function unidad() { return $this->belongsTo(Unidad::class); }
    public function servicio() { return $this->belongsTo(Servicio::class); }
    public function tipoMantenimiento() { return $this->belongsTo(TipoMantenimiento::class, 'tipo_mantenimiento_id'); }
    public function prioridad() { return $this->belongsTo(Prioridad::class); }
    public function creador() { return $this->belongsTo(User::class, 'creado_por'); }
    public function avances() { return $this->hasMany(Avance::class, 'solicitud_id'); }
}
