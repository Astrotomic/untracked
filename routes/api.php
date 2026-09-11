<?php

use App\Http\Controllers\ProcessedCollectController;
use App\Http\Controllers\RawCollectController;
use App\Http\Middleware\ValidateWebsiteOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(ValidateWebsiteOrigin::class)->group(function (): void {
    Route::post('/websites/{website}/collect/raw', RawCollectController::class)
        ->name('collect.raw');

    Route::post('/websites/{website}/collect/processed', ProcessedCollectController::class)
        ->name('collect.processed');
});
