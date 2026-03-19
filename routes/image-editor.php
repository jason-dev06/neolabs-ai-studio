<?php

use App\Http\Controllers\ImageEditor\ImageEditorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('image-editor')->group(function () {
    Route::get('/', [ImageEditorController::class, 'index'])->name('image-editor.index');
    Route::post('/', [ImageEditorController::class, 'store'])->name('image-editor.store');
    Route::get('/{session}', [ImageEditorController::class, 'show'])->name('image-editor.show');
    Route::post('/{session}/tools', [ImageEditorController::class, 'applyTool'])->name('image-editor.apply-tool');
    Route::post('/{session}/undo', [ImageEditorController::class, 'undo'])->name('image-editor.undo');
    Route::post('/{session}/redo', [ImageEditorController::class, 'redo'])->name('image-editor.redo');
    Route::delete('/{session}', [ImageEditorController::class, 'destroy'])->name('image-editor.destroy');
});
