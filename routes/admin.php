<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Owner-only routes
|--------------------------------------------------------------------------
| role:owner   -> Spatie's middleware; blocks anyone without that role.
| admin.idle   -> forces re-login after 30 idle minutes (see AdminIdleTimeout).
*/

Route::middleware(['auth', 'role:owner', 'admin.idle'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('/logout', [LogoutController::class, 'store'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');

        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::patch('/rooms/{room}/status', [RoomController::class, 'toggleStatus'])->name('rooms.toggleStatus');

        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])
            ->name('reservations.updateStatus');
        Route::patch('/reservations/{reservation}/mark-paid', [PaymentController::class, 'markReservationPaid'])
            ->name('reservations.markPaid');

        // Receipt review: approve (pay + confirm booking) or reject (cancel booking).
        Route::patch('/payments/{payment}/approve', [PaymentController::class, 'approve'])
            ->name('payments.approve');
        Route::patch('/payments/{payment}/reject', [PaymentController::class, 'reject'])
            ->name('payments.reject');
        Route::patch('/payments/{payment}/mark-paid', [PaymentController::class, 'markPaid'])
            ->name('payments.markPaid');
    });
