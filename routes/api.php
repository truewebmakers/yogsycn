<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('user/sendotp', [UserController::class, 'sendOTP']);
Route::post('user/verifyotp', [UserController::class, 'verifyOTP']);
Route::middleware('auth:api')->group(function () {
});
