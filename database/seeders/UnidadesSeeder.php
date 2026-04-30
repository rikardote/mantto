<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;

class UnidadesSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            'HG 5 DE DICIEMBRE',
            'HG FRAY JUNIPERO SERRA',
            'CH ENSENADA',
            'CMF MESA DE OTAY',
            'UMF LOS ALGODONES',
            'UMF ESTACION DELTA',
            'UMF SAN FELIPE',
            'UMF TECATE',
            'UMF SAN QUINTIN',
            'UMF ISLA CEDROS',
            'EBDI 34',
            'EBDI 59',
            'EBDI 60',
            'EBDI 105',
            'ALMACEN ESTATAL',
            'DELEGACION',
        ];

        foreach ($unidades as $nombre) {
            Unidad::create(['nombre' => $nombre, 'tipo' => 'Unidad Médica/Adm']);
        }
    }
}
