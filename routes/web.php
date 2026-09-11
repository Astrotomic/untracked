<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('websites.index'));
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::resource('websites', WebsiteController::class);
});
