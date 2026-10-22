<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:6,1')->name('register');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')->name('login');

    Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store'])
        ->middleware('throttle:6,1')->name('admin.login.attempt');
});

Route::post('/logout', [LogoutController::class, 'store'])
    ->middleware('auth')->name('logout');
