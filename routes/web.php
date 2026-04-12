<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\NpmController;

Route::get('/', [DefaultController::class, 'healthz']);
Route::options('/{path}', [DefaultController::class, 'cors'])->where('path', '.*');

Route::middleware(['cors'])->group(function () {
    Route::middleware(['auth:repo'])->group(function () {
        Route::get('/-/all', [NpmController::class, 'listAll']);
        Route::get('/{package}', [NpmController::class, 'getPackage'])->where('package', '.+');
    });

    Route::middleware(['auth:token'])->group(function () {
        Route::put('/{package}', [NpmController::class, 'publish'])->where('package', '.+');
    });
});
