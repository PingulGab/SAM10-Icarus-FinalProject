<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SentimentController;
use Illuminate\Support\Facades\Route;

// Default route for authenticated users
Route::get('/', function () {
    return redirect()->route('groups.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Group management routes
    Route::resource('groups', GroupController::class)->except(['show', 'edit']);
    
    // Sentiment analysis routes
    Route::post('sentiments/analyze', [SentimentController::class, 'analyze'])->name('sentiments.analyze');


});

Route::delete('/sentiments/{id}', [SentimentController::class, 'destroy'])->name('sentiments.destroy');
Route::delete('/sentiments/delete/delete-selected', [SentimentController::class, 'deleteSelected'])->name('sentiments.deleteSelected');

Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

Route::post('/sentiments/import', [SentimentController::class, 'import'])->name('sentiments.import');
Route::get('/sentiments/export', [SentimentController::class, 'export'])->name('sentiments.export');

require __DIR__.'/auth.php';
