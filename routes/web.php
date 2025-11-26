<?php

use App\Livewire\Memos\Memos;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('memo.store');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get('/verify-memo/{token}', [MemoVerificationController::class, 'verify'])->name('memo.verify');

require __DIR__.'/auth.php';
