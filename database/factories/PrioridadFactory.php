<?php

namespace Database\Factories;

use App\Models\Prioridad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prioridad>
 */
class PrioridadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Baja', 'Media', 'Alta', 'Urgente']),
            'tiempo_respuesta_horas' => $this->faker->randomElement([24, 48, 72]),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
