<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\HomeSliderController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\OTPController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

//user OTP
Route::post('user/sendotp', [OTPController::class, 'sendOTP']);
Route::post('user/verifyotp', [UserController::class, 'verifyOTP']);
Route::post('admin/login', [AdminController::class, 'adminLogin']);
Route::post('admin/register', [AdminController::class, 'adminRegistration']);

//admin sliders
Route::post('admin/slider/add', [HomeSliderController::class, 'addSlider']);
Route::post('admin/slider/delete', [HomeSliderController::class, 'deleteSlider']);

//onboardings
Route::get('/onboarding/getonboardings', [OnboardingController::class, 'getOnboardings']);
//admin
Route::post('admin/onboarding/add', [OnboardingController::class, 'addOnboarding']);
Route::post('admin/onboarding/update', [OnboardingController::class, 'updateOnboarding']);
Route::post('admin/onboarding/delete', [OnboardingController::class, 'deleteOnboarding']);



//admin onboardings

Route::middleware('auth:api')->group(function () {
    Route::get('slider/getsliders', [HomeSliderController::class, 'getSliders']);
});
