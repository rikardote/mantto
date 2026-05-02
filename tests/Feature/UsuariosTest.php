<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('solo supervisores pueden ver la lista de usuarios', function () {
    $supervisor = User::factory()->create(['rol' => 'supervisor']);
    $unidad = User::factory()->create(['rol' => 'unidad']);

    $this->actingAs($supervisor)
        ->get(route('usuarios.index'))
        ->assertOk();

    $this->actingAs($unidad)
        ->get(route('usuarios.index'))
        ->assertForbidden();
});
