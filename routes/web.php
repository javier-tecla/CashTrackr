<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Rutas para login
Route::get('/auth/register', [RegisterController::class, 'index'])->name('register');

//Rutas para Registrarse
Route::get('/auth/login', [LoginController::class, 'index'])->name('login');
