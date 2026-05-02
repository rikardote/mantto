<?php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test("solo supervisores pueden acceder a catalogos", function () {
    $supervisor = User::factory()->create(["rol" => "supervisor"]);
    $unidad = User::factory()->create(["rol" => "unidad"]);

    $this->actingAs($supervisor)
        ->get(route("catalogos.index"))
        ->assertOk();

    $this->actingAs($unidad)
        ->get(route("catalogos.index"))
        ->assertForbidden();
});
