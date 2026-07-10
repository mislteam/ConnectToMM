<?php

use App\Payment\Providers\Uab\Http\Controllers\AuthenticationController;
use App\Payment\Providers\Uab\Http\Controllers\HostedPaymentController;
use App\Payment\Providers\Uab\Http\Controllers\TransactionStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthenticationController::class, 'login']);
    });

    Route::prefix('payments')->group(function () {
        Route::post('/hosted-checkout', [HostedPaymentController::class, 'store']);
        Route::get('/status/{requestId}', [TransactionStatusController::class, 'show']);
    });
});
