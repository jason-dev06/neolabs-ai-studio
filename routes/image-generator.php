<?php

use App\Http\Controllers\ImageGenerator\ImageGeneratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('image-generator')->group(function () {
    Route::get('/', [ImageGeneratorController::class, 'index'])->name('image-generator.index');
    Route::post('/', [ImageGeneratorController::class, 'store'])->name('image-generator.store');
    Route::delete('/{generatedImage}', [ImageGeneratorController::class, 'destroy'])->name('image-generator.destroy');
});
