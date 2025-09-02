<?php

use App\Http\Controllers\Availability\AvailabilityController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Settings\SiteSettingController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Google\GoogleController;
use App\Http\Controllers\Site\FrontEndController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['logs']], function () {

    Route::get('/', [FrontEndController::class, 'frontendView'])->name('frontendView');

    Route::prefix('dashboard')->group(function () {

        Route::get('/login', [AuthController::class, 'loginView'])->name('loginView');
        Route::post('/login', [AuthController::class, 'login'])->name('login');


        Route::get('/forget-password', [AuthController::class, 'forgetPasswordView'])->name('forgetPasswordView');
        Route::get('/set-password/{email}/{token}', [AuthController::class, 'setPasswordView'])->name('setPasswordView');

        Route::post('/set-password', [AuthController::class, 'setPassword'])->name('setPassword');
        Route::post('/forget-password', [AuthController::class, 'forgetPassword'])->name('forgetPassword');


        Route::group(['middleware' => ['auth']], function () {

            Route::get('appointments', [AppointmentController::class, 'appointmentsView'])->name('appointmentsView');
            Route::get('/update-profile', [UserController::class, 'updateProfileView'])->name('updateProfileView');
            Route::post('/update-profile', [UserController::class, 'updateProfile'])->name('updateProfile');

            Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
            Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

            Route::group(['middleware' => ['admin']], function () {

                Route::get('/register', [UserController::class, 'registerView'])->name('registerView');
                Route::post('/register', [UserController::class, 'register'])->name('register');
                Route::get('/get-user', [UserController::class, 'getUser'])->name('getUser');
                Route::get('/update-user/{id}', [UserController::class, 'updateUserView'])->name('updateUserView');
                Route::post('/update-user', [UserController::class, 'updateUser'])->name('updateUser');


                Route::get('/update-setting', [SiteSettingController::class, 'updateSettingView'])->name('updateSettingView');
                Route::post('/update-setting', [SiteSettingController::class, 'updateSetting'])->name('updateSetting');

                Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('deleteUser');

                Route::get('update-appointments/{id}', [AppointmentController::class, 'updateAppointmentsView'])->name('updateAppointmentsView');
                Route::post('update-appointments', [AppointmentController::class, 'updateAppointments'])->name('updateAppointments');


                Route::get('availability/{id}', [AvailabilityController::class, 'availabilityView'])->name('availabilityView');
                Route::post('availability', [AvailabilityController::class, 'createAvailability'])->name('createAvailability');
                Route::post('/delete-availability', [AvailabilityController::class, 'deleteAvailability'])->name('availability.destroy');

                Route::get('transaction', [TransactionController::class, 'transactionView'])->name('transactionView');
                Route::post('transaction/update-status', [TransactionController::class, 'updateTransactionStatus'])->name('updateTransactionStatus');

            });
            Route::prefix('google')->group(function () {
                Route::get('/redirect', [GoogleController::class, 'redirectToGoogle'])->name('redirectToGoogle');
                Route::get('/callback', [GoogleController::class, 'handleGoogleCallback'])->name('handleGoogleCallback');
            });

        });

    });
});


Route::any('{any}', function () {
    if (auth()->user()) {
        session()->flash('errors', 'Requested path is not available');
        return redirect()->back();
    }
    return view('404');
})->where('any', '.*');
