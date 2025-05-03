<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login/username', [AuthController::class, 'loginWithUsername']);
Route::post('/login/phone', [AuthController::class, 'loginWithPhone']);
Route::post('/generate-otp', [AuthController::class, 'generateOtp']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/confirm-phone', [AuthController::class, 'confirmPhone']);

Route::resource('alerts', AlertController::class);
Route::resource('alert-types', AlertTypeController::class);
Route::get('types', [AlertController::class, 'getAlertTypes']);
Route::resource('user', UserController::class);

Route::get('alert/{id}/sendAlert', [AlertController::class, 'sendAlert']);