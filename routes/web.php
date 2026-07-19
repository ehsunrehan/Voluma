<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware([
    'auth',
    'verified'
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/history', [DashboardController::class, 'history'])
        ->name('history');

    Route::get('/profile', [DashboardController::class, 'profile'])
        ->name('profile');

});

require __DIR__.'/auth.php';