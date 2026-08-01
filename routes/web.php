<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ConvertController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/image-to-3d',[DashboardController::class,'index'])
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

    Route::post('/renew/model', [GenerationController::class,'renewModel']);

    Route::post('/download/model', [GenerationController::class,'downloadModel']);

    Route::get('/gallery', [GenerationController::class,'gallery'])->name('gallery');

    Route::get('/text-to-3d', [DashboardController::class,'textDashboard'])->name('text.dashboard');

    Route::post('/generate/text-model', [GenerationController::class,'generateFromText'])->name('generate.text.model');

    Route::get('/convert', [ConvertController::class, 'index'])->name('convert.index');

    Route::post('/convert/upload', [ConvertController::class, 'upload'])->name('convert.upload');

    Route::post('/convert/start', [ConvertController::class, 'start'])->name('convert.start');

    Route::get('/convert/status/{jobId}', [ConvertController::class, 'checkStatus'])->name('convert.status');

    Route::get('/convert/download/{jobId}', [ConvertController::class, 'download'])->name('convert.download');

});



