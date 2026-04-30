<?php

namespace App\Http\Controllers;

use App\Models\SolicitudMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudExportController extends Controller
{
    public function export()
    {
        $user = Auth::user();
        $query = SolicitudMantenimiento::with(['unidad', 'servicio', 'prioridad', 'tipoMantenimiento', 'creador']);

        // Filter if not supervisor
        if ($user->rol === 'unidad') {
            $query->where('unidad_id', $user->unidad_id);
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->get();

        $filename = "reporte_mantenimiento_" . date('Y-m-d_H-i') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($handle, [
            'ID',
            'Folio/Oficio',
            'Unidad/Área',
            'Título',
            'Descripción',
            'Tipo de Servicio',
            'Tipo Mantenimiento',
            'Prioridad',
            'Estatus',
            'Orden de Servicio',
            'Fecha Solicitud',
            'Fecha Atención',
            'Fecha Cierre',
            'SLA Límite',
            'Creado Por',
            'Días Transcurridos'
        ]);

        foreach ($solicitudes as $s) {
            $dias = $s->fecha_cierre 
                ? $s->fecha_solicitud->diffInDays($s->fecha_cierre) 
                : $s->fecha_solicitud->diffInDays(now());

            fputcsv($handle, [
                $s->id,
                $s->folio_oficio,
                $s->unidad->nombre,
                $s->titulo,
                $s->descripcion,
                $s->servicio->nombre . ($s->descripcion_servicio_otro ? " ({$s->descripcion_servicio_otro})" : ""),
                $s->tipoMantenimiento->nombre,
                $s->prioridad->nombre,
                strtoupper($s->estatus),
                $s->orden_servicio,
                $s->fecha_solicitud->format('d/m/Y H:i'),
                $s->fecha_atencion ? $s->fecha_atencion->format('d/m/Y H:i') : 'N/A',
                $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : 'N/A',
                $s->fecha_limite->format('d/m/Y H:i'),
                $s->creador->name,
                $dias
            ]);
        }

        fclose($handle);
        exit;
    }
}
