<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\LoginController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\SliderController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\InvoiceController;

Route::prefix('admin')->group(function () {
    // route login
    Route::POST('/login', [LoginController::class, 'index'], ['as' => 'admin']);

    Route::group(['middleware' => 'auth:api_admin'], function () {
       
        // data user
        Route::GET('/user', [LoginController::class, 'getUser'], ['as' => 'admin']);

        // refreshToken
        Route::GET('/refresh', [LoginController::class, 'refreshToken'], ['as' => 'admin']);
        
        // route logout
        Route::POST('/logout', [LoginController::class, 'logout'], ['as' => 'admin']);

        // route category
        Route::resource('/categories', CategoryController::class, ['except' => ['create', 'edit'], 'as' => 'admin']);

        // route product
        Route::resource('/products', ProductController::class, ['except' => ['create', 'edit'], 'as' => 'admin']);

        // route invoice
        Route::resource('/invoices', InvoiceController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy'], 'as' => 'admin']);
        
        // // route user
        // Route::resource('/users', UserController::class, ['except' => ['create', 'edit'], 'as' => 'admin']);

        // // route slider
        // Route::resource('/sliders', SliderController::class, ['except' => ['create', 'edit', 'show', 'update'], 'as' => 'admin']);

        // // route customer
        // Route::GET('/customers', [CustomerController::class, 'index'], ['as' => 'admin']);
    });
});