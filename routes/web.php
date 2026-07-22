<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerationController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard',[DashboardController::class,'index'])
        ->name('dashboard');

    Route::get('/history',[DashboardController::class,'history'])
        ->name('history');

    Route::get('/profile',[DashboardController::class,'profile'])
        ->name('profile');

    Route::post('/generate',
        [GenerationController::class,'store']
    )->name('generate.store');

    Route::post('/generate/model',
        [GenerationController::class,'generate']
    )->name('generate.model');

    Route::get('/generate/status/{taskId}',
        [GenerationController::class,'checkStatus']
    )->name('generate.status');

    Route::get('/model/download/{taskId}',
        [GenerationController::class,'downloadModel']
    )->name('model.download');

    Route::get('/stream-model/{taskId}', [GenerationController::class, 'streamModel']);

    Route::post('/credits/deduct',
        [GenerationController::class,'deductCredits']
    )->name('credits.deduct');

});



