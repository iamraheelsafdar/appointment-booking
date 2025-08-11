<?php

use App\Http\Controllers\Site\FrontEndController;
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

Route::group(['middleware' => ['logs']], function () {
    Route::post('book-appointment', [FrontEndController::class, 'bookAppointment']);

    Route::post('stripe-webhook', [FrontEndController::class, 'handleWebhook']);
    Route::post('test', [FrontEndController::class, 'test']);
});
