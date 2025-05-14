<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::domain('domain1.test')->group(function () {
//     Route::get('/', [App\Http\Controllers\Domain1\HomeController::class, 'index']);
// });

// Route::domain('domain2.test')->group(function () {
//     Route::get('/', [App\Http\Controllers\Domain2\HomeController::class, 'index']);
// });

// Route::domain('domain3.test')->group(function () {
//     Route::get('/', [App\Http\Controllers\Domain3\HomeController::class, 'index']);
// });

Route::domain(config('app.domains.domain1'))->group(function () {
    Route::get('/', [App\Http\Controllers\Domain1\HomeController::class, 'index']);

});

Route::domain(config('app.domains.domain2'))->group(function () {
    Route::get('/', [App\Http\Controllers\Domain1\HomeController::class, 'index']);

});

Route::domain(config('app.domains.domain3'))->group(function () {
    Route::get('/', [App\Http\Controllers\Domain1\HomeController::class, 'index']);

});
