<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // चैट पेज देखने के लिए
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    
    // किसी यूज़र के साथ पुरानी चैट हिस्ट्री मंगाने के लिए
    Route::get('/chat/{user}', [ChatController::class, 'fetchMessages']);
    
    // नया मैसेज भेजने के लिए
    Route::post('/chat', [ChatController::class, 'sendMessage']);
});

require __DIR__.'/auth.php';
