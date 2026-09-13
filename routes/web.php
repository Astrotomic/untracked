<?php

use App\Http\Controllers\Auth\LoginUserController;
use App\Http\Controllers\Auth\LogoutUserController;
use App\Http\Controllers\CreateWebsiteController;
use App\Http\Controllers\DeleteWebsiteController;
use App\Http\Controllers\ListWebsitesController;
use App\Http\Controllers\ShowWebsiteController;
use App\Http\Controllers\ShowWebsiteEditFormController;
use App\Http\Controllers\TrackTestRequestController;
use App\Http\Controllers\UpdateWebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/test', TrackTestRequestController::class)->name('test');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', LoginUserController::class)->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('websites.index'));
    Route::post('/logout', LogoutUserController::class)->name('logout');

    Route::get('/websites', ListWebsitesController::class)->name('websites.index');
    Route::view('/websites/create', 'websites.create')->name('websites.create');
    Route::post('/websites', CreateWebsiteController::class)->name('websites.store');
    Route::get('/websites/{website}', ShowWebsiteController::class)->name('websites.show');
    Route::get('/websites/{website}/edit', ShowWebsiteEditFormController::class)->name('websites.edit');
    Route::match(['put', 'patch'], '/websites/{website}', UpdateWebsiteController::class)->name('websites.update');
    Route::delete('/websites/{website}', DeleteWebsiteController::class)->name('websites.destroy');
});
