<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

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

it('registers a new user as unverified and dispatches the registered event', function() {

    Event::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Javier Borjas',
        'email' => 'cristman11@gmail.com',
        'password' => 'Key123456789$',
        'password_confirmation' => 'Key123456789$'
    ]);

    $response->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'cristman11@gmail.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Javier Borjas');
    expect($user->email)->toBe('cristman11@gmail.com');
    expect($user->hasVerifiedEmail())->toBeFalse();

    Event::assertDispatched(Registered::class);
});


