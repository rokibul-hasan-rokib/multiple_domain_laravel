<?php

use App\Http\Controllers\StripeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('pay-gate/{order}', [StripeApiController::class, 'payGate']);
Route::get('paygate-callback', [StripeApiController::class, 'paygateCallback'])->name('paygate.callback');


Route::get('stripe', [StripeApiController::class, 'createCheckout']);
Route::post('stripe-webhook/success', [StripeApiController::class, 'webhookSuccess']);
Route::post('stripe-webhook/invoice-paid', [StripeApiController::class, 'invoicePaid']);