<?php

namespace Database\Factories;

use App\Models\Avance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Avance>
 */
class AvanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_id' => \App\Models\SolicitudMantenimiento::inRandomOrder()->first()?->id ?? \App\Models\SolicitudMantenimiento::factory(),
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id ?? \App\Models\User::factory(),
            'comentario' => $this->faker->paragraph(),
            'porcentaje' => $this->faker->numberBetween(0, 100),
            'fecha' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
