<?php

use App\Http\Controllers\VideoGenerator\VideoGeneratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('video-generator')->group(function () {
    Route::get('/', [VideoGeneratorController::class, 'index'])->name('video-generator.index');
    Route::post('/', [VideoGeneratorController::class, 'store'])->name('video-generator.store');
    Route::delete('/{generatedVideo}', [VideoGeneratorController::class, 'destroy'])->name('video-generator.destroy');
});
