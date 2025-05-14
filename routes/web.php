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


Route::middleware(['detect.domain'])->group(function () {

    // Domain 1 Routes
    Route::domain(parse_url(config('app.domains.domain1'), PHP_URL_HOST))->group(function () {
        Route::get('/', function () {
            return view('domain1');
        });

        Route::get('/about', function () {
            return 'About page for domain1';
        });
    });


    Route::domain(parse_url(config('app.domains.domain2'), PHP_URL_HOST))->group(function () {
        Route::get('/', function () {
            return view('domain2');
        });

        Route::get('/contact', function () {
            return 'Contact page for domain2';
        });
    });

    // Domain 3 Routes
    Route::domain(parse_url(config('app.domains.domain3'), PHP_URL_HOST))->group(function () {
        Route::get('/', function () {
            return view('domain3');
        });

        Route::get('/services', function () {
            return 'Services page for domain3';
        });
    });

});