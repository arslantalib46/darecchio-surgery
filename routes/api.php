<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
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


// Route for registering a user
Route::post('/signup', [AuthController::class ,  'signUp']);

// Route for logging in a user
Route::post('/login', [AuthController::class ,  'login']);

// Route for sending OTP to a user's email
Route::post('/sendOTP', [AuthController::class ,  'sendOTP']);

// Route for verifying OTP and confirming a user's profile
Route::post('/verifyOTP', [AuthController::class ,  'verifyOTP']);

// Route::middleware('auth:api')->get('/profile', [AuthController::class ,  'profile']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::get('profile', [AuthController::class ,  'profile']);
});