<?php

use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\YogaPoseController;
use Illuminate\Support\Facades\Route;


Route::get('video/getall', [VideoController::class, 'getAllVideos']);

Route::prefix('admin')->group(function () {
    //videos
    Route::post('video/add', [VideoController::class, 'addVideo']);
    Route::post('video/update', [VideoController::class, 'updateVideo']);
    Route::post('video/delete', [VideoController::class, 'deleteVideo']);

    //artical category
    Route::post('articalcategory/add', [ArticleCategoryController::class, 'addCategory']);
    Route::post('articalcategory/update', [ArticleCategoryController::class, 'updateCategory']);
    Route::post('articalcategory/delete', [ArticleCategoryController::class, 'deleteCategory']);
    Route::get('articalcategory/getall', [ArticleCategoryController::class, 'getAllCategoriesAdmin']);


    //artical
    Route::post('artical/add', [ArticleController::class, 'addArticle']);
    Route::post('artical/update', [ArticleController::class, 'updateArticle']);
    Route::post('artical/delete', [ArticleController::class, 'deleteArticle']);
    Route::get('artical/get', [ArticleController::class, 'getAllArticlesAdmin']);

    //yoga poses category
    Route::post('posecategory/add', [YogaPoseController::class, 'addPoseCategory']);
    Route::post('posecategory/update', [YogaPoseController::class, 'updatePoseCategory']);
    Route::post('posecategory/delete', [YogaPoseController::class, 'deletePoseCategory']);
    Route::get('posecategory/getall', [YogaPoseController::class, 'getAllPosesCategoriesAdmin']);

    //yoga pose
    Route::post('yogapose/add', [YogaPoseController::class, 'addYogaPose']);
    Route::post('yogapose/update', [YogaPoseController::class, 'updateYogaPose']);
    Route::post('yogapose/delete', [YogaPoseController::class, 'deleteYogaPose']);
    Route::get('yogapose/bycategory/get', [YogaPoseController::class, 'getAllYogaPosesByCategory']);
    Route::get('yogapose/getall', [YogaPoseController::class, 'getAllYogaPosesAdmin']);
});

Route::prefix('user')->group(function () {
    Route::get('artical/getall', [ArticleController::class, 'getAllArticlesUser']);
    Route::get('artical/latest/get', [ArticleController::class, 'getLatestArticles']);
    Route::get('artical/expertapproved/get', [ArticleController::class, 'getExpertApprovedArticles']);
    Route::get('articalcategory/getall', [ArticleCategoryController::class, 'getAllCategoriesUser']);
    Route::get('posecategory/getall', [YogaPoseController::class, 'getAllPosesCategoriesUser']);
    Route::get('yogapose/getall', [YogaPoseController::class, 'getAllYogaPosesUser']);
    Route::get('yogapose/details/get', [YogaPoseController::class, 'getYogaPoseDetails']);

    Route::get('articalcategory/get/{id}', [ArticleCategoryController::class, 'getAllCategoriesAdmin']);
    // Route::get('yogapose/details/{id}', [YogaPoseController::class, 'getYogaPoseDetails']);
});
