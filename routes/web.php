<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DefaultController;
use App\Http\Controllers\NpmController;

Route::get('/', [DefaultController::class, 'healthz']);
Route::options('/{path}', [DefaultController::class, 'cors'])->where('path', '.*');

Route::middleware(['cors'])->group(function () {
    // Public read routes (repo guard - allows anonymous for public packages)
    Route::middleware(['auth:repo'])->group(function () {
        Route::get('/-/all', [NpmController::class, 'listAll']);
        Route::get('/{package}/-/{filename}', [NpmController::class, 'downloadTarball'])->where(['package' => '.+', 'filename' => '.+\.tgz']);
        Route::get('/{package}', [NpmController::class, 'getPackage'])->where('package', '.+');
    });

    // Authenticated write routes
    Route::middleware(['auth:token'])->group(function () {
        Route::put('/{package}', [NpmController::class, 'publish'])->where('package', '.+');
    });

    // NPM audit stub
    Route::post('/-/npm/v1/security/audits', [NpmController::class, 'auditStub']);
});
