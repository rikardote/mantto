<?php

namespace Database\Factories;

use App\Models\SolicitudMantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudMantenimiento>
 */
class SolicitudMantenimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unidad_id' => \App\Models\Unidad::inRandomOrder()->first()?->id ?? \App\Models\Unidad::factory(),
            'servicio_id' => \App\Models\Servicio::inRandomOrder()->first()?->id ?? \App\Models\Servicio::factory(),
            'tipo_mantenimiento_id' => \App\Models\TipoMantenimiento::inRandomOrder()->first()?->id ?? \App\Models\TipoMantenimiento::factory(),
            'prioridad_id' => \App\Models\Prioridad::inRandomOrder()->first()?->id ?? \App\Models\Prioridad::factory(),
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(),
            'descripcion_servicio_otro' => $this->faker->optional(0.2)->sentence(),
            'folio_oficio' => 'OF-' . $this->faker->numerify('####/2026'),
            'orden_servicio' => 'OS-' . $this->faker->numerify('####'),
            'estatus' => $this->faker->randomElement(['abierto', 'validado', 'asignado', 'en_proceso', 'terminado']),
            'fecha_solicitud' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha_limite' => $this->faker->dateTimeBetween('now', '+2 weeks'),
            'creado_por' => \App\Models\User::inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
        ];
    }
}
