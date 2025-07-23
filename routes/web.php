<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Auth\AuthController;
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

    Route::get('/test', function (){
       return view('backend.mail.invite-user' , ['user' => '1']);
    });

    Route::prefix('dashboard')->group(function () {

        Route::get('/login', [AuthController::class, 'loginView'])->name('loginView');
        Route::post('/login', [AuthController::class, 'login'])->name('login');


        Route::get('/forget-password', function () {
            return view('backend.auth.forget-password');
        })->name('forgetPassword');


        Route::get('/set-password/{email}/{token}', [AuthController::class, 'setPasswordView'])->name('setPasswordView');

        Route::post('/set-password', [AuthController::class, 'setPassword'])->name('setPassword');



    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/register', [UserController::class, 'registerView'])->name('registerView');
        Route::post('/register', [UserController::class, 'register'])->name('register');
        Route::get('/get-user', [UserController::class, 'getUser'])->name('getUser');

    });
});


Route::any('{any}', function () {
    if (auth()->user()) {
        session()->flash('errors', 'Requested path is not available');
        return redirect()->back();
    }
    return view('404');
})->where('any', '.*');
