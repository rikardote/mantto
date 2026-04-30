<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UnidadesSeeder::class,
            CatalogoSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Supervisor General',
            'email' => 'admin@mantenimiento.com',
            'password' => Hash::make('password'),
            'rol' => 'supervisor',
            'unidad_id' => null,
        ]);

        // Create a unit user for testing
        User::factory()->create([
            'name' => 'Usuario HG 5',
            'email' => 'hg5@mantenimiento.com',
            'password' => Hash::make('password'),
            'rol' => 'unidad',
            'unidad_id' => 1,
        ]);
    }
}
