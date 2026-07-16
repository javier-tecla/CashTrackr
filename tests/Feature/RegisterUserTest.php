<?php

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('shows the registration screen', function () {
    $response = $this->get(route('register'));

    $response->assertOk();

    $response->assertStatus(200);
    $response->assertSee('Crear Cuenta');
    $response->assertSee('Registrarme');

    $response->assertSeeInOrder([
        'Crear Cuenta',
        'Registrarme',
    ]);

});

it('registers a new user as unverified and dispatches the registered event', function () {

    Event::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Javier Borjas',
        'email' => 'cristman11@gmail.com',
        'password' => 'Key123456789$',
        'password_confirmation' => 'Key123456789$',
    ]);

    $response->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'cristman11@gmail.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Javier Borjas');
    expect($user->email)->toBe('cristman11@gmail.com');
    expect($user->hasVerifiedEmail())->toBeFalse();

    Event::assertDispatched(Registered::class);
});

it('should validate required fields when the request body is empty', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors([
        'name',
        'email',
        'password',
    ]);

    $response->assertSessionHasErrors([
        'name' => 'El Nombre es obligatorio',
        'email' => 'El E-mail es obligatorio',
        'password' => 'La Contraseña es obligatoria',
    ]);
});

// it('prevents duplicate email addresses', function() {

//     User::factory()->create([
//         'email' => 'cristman11@gmail.com'
//     ]);

//     $response = $this->post(route('register.store'), [
//         'name' => 'Javier Borjas',
//         'email' => 'cristman11@gmail.com',
//         'password' => 'Key123456789$',
//         'password_confirmation' => 'Key123456789$',
//     ]);

//     $response->assertRedirect();

//     $response->assertSessionHasErrors([
//         'email' => 'Este correo ya está registrado',
//     ]);

// });

it('sends the verification email notification after registration', function() {
     Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Javier Borjas',
        'email' => 'cristman11@gmail.com',
        'password' => 'Key123456789$',
        'password_confirmation' => 'Key123456789$',
    ]);

    $user = User::where('email', 'cristman11@gmail.com')->first();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('verifies the user email from a signed verification link', function() {

    $user = User::factory()->unverified()->create();

     $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('dashboard'));
        expect($user->hasVerifiedEmail())->toBeTrue();
});

it('does not allow an unverified user to access the dashboard', function() {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

it('allows a verified user to access the dashboard', function() {
     $user = User::factory()->create([
        'email_verified_at' => now()
     ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});
