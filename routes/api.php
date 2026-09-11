<?php

use App\Http\Controllers\ProcessedCollectController;
use App\Http\Controllers\RawCollectController;
use Illuminate\Support\Facades\Route;

Route::post('/websites/{website}/collect/raw', RawCollectController::class)
    ->name('collect.raw');

Route::post('/websites/{website}/collect/processed', ProcessedCollectController::class)
    ->name('collect.processed');
