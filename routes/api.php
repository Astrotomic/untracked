<?php

use App\Http\Controllers\CollectController;
use Illuminate\Support\Facades\Route;

Route::post('/websites/{website}/collect', CollectController::class)
    ->name('collect');
