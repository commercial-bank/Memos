<?php

use App\Livewire\Memos\Memos;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\VerificationController;

Route::view('/', 'pdf/t')->name('memo.store');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// Route publique pour la vérification du QR Code
Route::get('/verifier-document/{token}', [VerificationController::class, 'verify'])
     ->name('memo.verify');    

Route::get('/memos/{id}/print', [MemoController::class, 'print'])->name('memos.print')->middleware('auth');


require __DIR__.'/auth.php';
