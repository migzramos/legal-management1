<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MessageController;

Route::middleware(['auth', 'active.user'])->group(function () {
    // Message endpoints for real-time messaging
    Route::prefix('appointments/{appointment}')
        ->name('appointments.')
        ->group(function () {
            // Get messages in thread (with polling support via since_id query param)
            Route::get('messages', [MessageController::class, 'getThread'])
                ->name('messages.index');

            // Send message
            Route::post('messages', [MessageController::class, 'send'])
                ->name('messages.store');

            // Mark thread messages as read
            Route::post('messages/mark-read', [MessageController::class, 'markAsRead'])
                ->name('messages.mark-read');

            // Get thread summary
            Route::get('messages/summary', [MessageController::class, 'getSummary'])
                ->name('messages.summary');

            // Search messages in thread
            Route::get('messages/search', [MessageController::class, 'search'])
                ->name('messages.search');
        });

    // Latest messages across all appointments
    Route::get('messages/latest', [MessageController::class, 'getLatest'])
        ->name('messages.latest');

    // Delete message
    Route::delete('messages/{message}', [MessageController::class, 'delete'])
        ->name('messages.destroy');

    // Restore deleted message
    Route::post('messages/{message}/restore', [MessageController::class, 'restore'])
        ->name('messages.restore');
});
