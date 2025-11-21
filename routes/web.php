<?php

use App\Livewire\Memos\Memos;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::post('/memo/store', [Memos::class,'store'])->name('memo.store');

require __DIR__.'/auth.php';
