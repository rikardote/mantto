<?php

namespace Database\Seeders;

use App\Models\Servicio;
use App\Models\TipoMantenimiento;
use App\Models\Prioridad;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // Servicios
        $servicios = [
            'Aire acondicionado / HVAC',
            'Fontanería / Hidráulico',
            'Eléctrico',
            'Calderas',
            'Civil / Pintura / Impermeabilización',
            'Electromecánico',
            'Biomédico',
            'Otro',
        ];
        foreach ($servicios as $s) Servicio::create(['nombre' => $s]);

        // Tipos de Mantenimiento
        $tipos = ['Preventivo', 'Correctivo', 'Predictivo', 'Emergencia'];
        foreach ($tipos as $t) TipoMantenimiento::create(['nombre' => $t]);

        // Prioridades
        Prioridad::create(['nombre' => 'Alta', 'tiempo_respuesta_horas' => 24, 'descripcion' => 'Inmediata']);
        Prioridad::create(['nombre' => 'Media', 'tiempo_respuesta_horas' => 72, 'descripcion' => '48–72 hrs']);
        Prioridad::create(['nombre' => 'Baja', 'tiempo_respuesta_horas' => 168, 'descripcion' => 'Programable (1 semana)']);
    }
}
