<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Routes pour l'authentification des invités (non connectés)
Route::middleware('guest')->group(function () {
    // Inscription
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    // Connexion
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});


// Routes pour les utilisateurs connectés
Route::middleware('auth')->group(function () {
    // Déconnexion
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

   
});

 // Tableau de bord (exemple de page protégée)
    Route::get('/dashboard', function () {
        return view('livewire/dashboard'); // Nous allons créer cette vue simple
    })->name('dashboard');