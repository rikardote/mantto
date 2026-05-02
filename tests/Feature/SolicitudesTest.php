<?php
use App\Models\User;
use App\Models\SolicitudMantenimiento;
use App\Models\Unidad;
use App\Models\Servicio;
use App\Models\Prioridad;
use App\Models\TipoMantenimiento;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->unidad = Unidad::factory()->create();
    $this->servicio = Servicio::factory()->create();
    $this->prioridad = Prioridad::factory()->create();
    $this->tipo = TipoMantenimiento::factory()->create();
    
    $this->user->update(['unidad_id' => $this->unidad->id]);
});

test('página de solicitudes carga correctamente', function () {
    $this->actingAs($this->user)
        ->get(route('solicitudes.index'))
        ->assertOk();
});

test('se puede ver una solicitud específica', function () {
    $solicitud = SolicitudMantenimiento::factory()->create([
        'creado_por' => $this->user->id,
        'unidad_id' => $this->unidad->id,
        'servicio_id' => $this->servicio->id,
        'prioridad_id' => $this->prioridad->id,
        'tipo_mantenimiento_id' => $this->tipo->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('solicitudes.show', $solicitud))
        ->assertOk()
        ->assertSee($solicitud->titulo);
});

test('se puede agregar un avance a una solicitud', function () {
    $solicitud = SolicitudMantenimiento::factory()->create([
        'creado_por' => $this->user->id,
        'unidad_id' => $this->unidad->id,
        'servicio_id' => $this->servicio->id,
        'prioridad_id' => $this->prioridad->id,
        'tipo_mantenimiento_id' => $this->tipo->id,
        'estatus' => 'abierto'
    ]);

    Volt::actingAs($this->user)
        ->test('solicitudes.show', ['solicitud' => $solicitud])
        ->set('comentario_avance', 'Iniciando mantenimiento')
        ->set('porcentaje_avance', 10)
        ->call('agregarAvance')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('avances', [
        'solicitud_id' => $solicitud->id,
        'comentario' => 'Iniciando mantenimiento',
        'porcentaje' => 10
    ]);
});

test('se puede cambiar el estatus de una solicitud', function () {
    $solicitud = SolicitudMantenimiento::factory()->create([
        'creado_por' => $this->user->id,
        'unidad_id' => $this->unidad->id,
        'servicio_id' => $this->servicio->id,
        'prioridad_id' => $this->prioridad->id,
        'tipo_mantenimiento_id' => $this->tipo->id,
        'estatus' => 'abierto'
    ]);

    Volt::actingAs($this->user)
        ->test('solicitudes.show', ['solicitud' => $solicitud])
        ->call('cambiarEstatus', 'validado')
        ->assertHasNoErrors();

    expect($solicitud->refresh()->estatus)->toBe('validado');
});
