<?php

namespace Database\Factories;

use App\Models\TipoMantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoMantenimiento>
 */
class TipoMantenimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Preventivo', 'Correctivo', 'Predictivo']),
            'activo' => true,
        ];
    }
}
