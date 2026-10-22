<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/rooms/book', [BookingController::class, 'store'])->name('rooms.book');

    // Keep the existing endpoint available for clients already using it.
    Route::post('/reservations', [BookingController::class, 'store'])->name('reservations.store');
});
