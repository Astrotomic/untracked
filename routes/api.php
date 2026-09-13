<?php

use App\Http\Controllers\CollectProcessedMetricsController;
use App\Http\Controllers\CollectRawMetricsController;
use App\Http\Middleware\ValidateWebsiteOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(ValidateWebsiteOrigin::class)->group(function (): void {
    Route::post('/websites/{website}/collect/raw', CollectRawMetricsController::class)->name('collect.raw');
    Route::post('/websites/{website}/collect/processed', CollectProcessedMetricsController::class)->name('collect.processed');
});
