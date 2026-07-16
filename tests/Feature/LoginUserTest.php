<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the login screen', function() {
    $response = $this->get(route('login'));

    $response->assertOk();
});

it('logs in a verified user successfully', function() {
    $user = User::factory()->create([
        'email' => 'juan@juan.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'juan@juan.com',
        'password' => 'password'
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

});

it('does not log in with invalid credentials', function() {
    User::factory()->create([
        'email' => 'juan@juan.com',
        'password' => bcrypt('password')
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'juan@juan.com',
        'password' => 'incorrect-password'
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Credenciales Incorrectas');

    $this->assertGuest();

});

it('prevents unverified user from accessing dashboard', function() {
    User::factory()->unverified()->create([
        'email' => 'juan@juan.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'juan@juan.com',
        'password' => 'password'
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

     $dashboardResponse = $this->get(route('dashboard'));
    $dashboardResponse->assertRedirect(route('verification.notice'));
});

it('does not allow access to dashboard if email is not verifield', function() {
    $user = User::factory()->create([
        'email_verified_at' => null
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

it('allow access to dashboard if email is verifield', function() {
    $user = User::factory()->create([
        'email_verified_at' => now()
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOK();
});

it('fails login if user does not exist', function() {
    $response = $this->from(route('login'))
                    ->post(route('login.store'), [
                        'email' => 'noexiste@dominio.com',
                        'password' => 'password'
                    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => 'No encontramos una cuenta con ese correo electrónico.'
    ]);

    $this->assertGuest();
});

