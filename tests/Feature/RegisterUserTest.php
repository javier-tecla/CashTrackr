<?php

it('shows the registration screen', function() {
    $response = $this->get(route('register'));

    $response->assertOk();

    $response->assertStatus(200);
    $response->assertSee('Crear Cuenta');
    $response->assertSee('Registrarme');

    $response->assertSeeInOrder([
        'Crear Cuenta',
        'Registrarme'
    ]);

});


