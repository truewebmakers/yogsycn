<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\HomeSliderController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\OTPController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSizeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//user OTP
Route::post('user/sendotp', [OTPController::class, 'sendOTP']);
Route::post('user/verifyotp', [UserController::class, 'verifyOTP']);
//admin
Route::post('admin/login', [AdminController::class, 'adminLogin']);
Route::post('admin/register', [AdminController::class, 'adminRegistration']);

//admin sliders
Route::post('admin/slider/add', [HomeSliderController::class, 'addSlider']);
Route::post('admin/slider/delete', [HomeSliderController::class, 'deleteSlider']);
//both
Route::get('slider/getsliders', [HomeSliderController::class, 'getSliders']);

//both
Route::get('/onboarding/getonboardings', [OnboardingController::class, 'getOnboardings']);
//admin
Route::post('admin/onboarding/add', [OnboardingController::class, 'addOnboarding']);
Route::post('admin/onboarding/update', [OnboardingController::class, 'updateOnboarding']);
Route::post('admin/onboarding/delete', [OnboardingController::class, 'deleteOnboarding']);

//product category
//admin
Route::post('admin/productcategory/add', [ProductCategoryController::class, 'addProductCategory']);
Route::post('admin/productcategory/update', [ProductCategoryController::class, 'updateProductCategory']);
Route::post('admin/productcategory/delete', [ProductCategoryController::class, 'deleteProductCategory']);
//both
Route::get('productcategory/getallcategories', [ProductCategoryController::class, 'getAllProductCategories']);

//product
Route::post('admin/product/add', [ProductController::class, 'addProduct']);
Route::post('admin/product/update', [ProductController::class, 'updateProduct']);
Route::post('admin/product/delete', [ProductController::class, 'deleteProduct']);
//customer
Route::get('user/product/getproductsbycategory', [ProductController::class, 'getAllProductByCategoryCustomer']);
Route::get('user/product/getbestsellers', [ProductController::class, 'getBestSeller']);
Route::get('user/product/search', [ProductController::class, 'searchProduct']);
//admin
Route::get('admin/product/getproductsbycategory', [ProductController::class, 'getAllProductByCategoryAdmin']);

//product size
Route::post('admin/product/size/add', [ProductSizeController::class, 'addProductSize']);
Route::post('admin/product/size/update', [ProductSizeController::class, 'updateProductSize']);
Route::post('admin/product/size/delete', [ProductSizeController::class, 'deleteProductSize']);

//profile
Route::post('user/profile/update', [UserController::class, 'updateProfile']);


//admin onboardings

// Route::middleware('auth:api')->group(function () {
//     Route::get('slider/getsliders', [HomeSliderController::class, 'getSliders']);
// });

// Route::middleware(['admin'])->group(function () {
// // Route::middleware(['auth:admin', 'admin'])->group(function () {
//     // Route::post('admin/slider/add', [HomeSliderController::class, 'addSlider']);
// });