<?php

it('redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirectToRoute('login');
});
