<?php

use App\Http\Controllers\PaymentController;

Route::post('/payment/telebirr', [PaymentController::class, 'processTelebirr']);
Route::post('/payment/cbe', [PaymentController::class, 'processCBE']);
Route::post('/payment/chapa/initialize', [PaymentController::class, 'initializeChapa']);