<?php

use App\Http\Controllers\VideoEditor\VideoEditorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('video-editor')->group(function () {
    Route::get('/', [VideoEditorController::class, 'index'])->name('video-editor.index');
    Route::post('/', [VideoEditorController::class, 'store'])->name('video-editor.store');
    Route::get('/{session}', [VideoEditorController::class, 'show'])->name('video-editor.show');
    Route::post('/{session}/tools', [VideoEditorController::class, 'applyTool'])->name('video-editor.apply-tool');
    Route::post('/{session}/undo', [VideoEditorController::class, 'undo'])->name('video-editor.undo');
    Route::post('/{session}/redo', [VideoEditorController::class, 'redo'])->name('video-editor.redo');
    Route::delete('/{session}', [VideoEditorController::class, 'destroy'])->name('video-editor.destroy');
});
