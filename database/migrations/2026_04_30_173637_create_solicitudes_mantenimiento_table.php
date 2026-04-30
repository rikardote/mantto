<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('tipo_mantenimiento_id')->constrained('tipos_mantenimiento');
            $table->foreignId('prioridad_id')->constrained('prioridades');
            
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('descripcion_servicio_otro')->nullable();
            
            $table->string('folio_oficio')->nullable();
            $table->string('orden_servicio')->nullable();
            
            $table->string('estatus')->default('abierto'); // abierto, validado, asignado, en_proceso, terminado
            
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_atencion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamp('fecha_limite')->nullable();
            
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_mantenimiento');
    }
};
