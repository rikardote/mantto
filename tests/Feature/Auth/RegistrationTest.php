<?php

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

test('registration is not enabled', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
});
