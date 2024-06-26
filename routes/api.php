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

use App\Http\Controllers\Api\Customer\LoginController as CustomerLoginController;
use App\Http\Controllers\Api\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Api\Customer\RegisterController;

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
        
        // route customer
        Route::GET('/customers', [CustomerController::class, 'index'], ['as' => 'admin']);
        
        // route slider
        Route::resource('/sliders', SliderController::class, ['except' => ['create', 'edit', 'show', 'update'], 'as' => 'admin']);
        
        // route user
        Route::resource('/users', UserController::class, ['except' => ['create', 'edit'], 'as' => 'admin']);

        // route dashboard
        Route::GET('/dashboard', [App\Http\Controllers\Api\Admin\DashboardController::class, 'index', ['as' => 'admin']]);
    });
});

Route::prefix('customer')->group(function () {

    Route::POST('/register', [RegisterController::class, 'store'], ['as' => 'customer']);

    Route::POST('/login', [CustomerLoginController::class, 'index'], ['as' => 'customer']);

    Route::group(['middleware' => 'auth:api_customer'], function () {
       
        // data user
        Route::GET('/user', [CustomerLoginController::class, 'getUser'], ['as' => 'customer']);

        // refreshToken
        Route::GET('/refresh', [CustomerLoginController::class, 'refreshToken'], ['as' => 'customer']);
        
        // route logout
        Route::POST('/logout', [CustomerLoginController::class, 'logout'], ['as' => 'customer']);

        // route invoice
        Route::resource('/invoices', CustomerInvoiceController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy'], 'as' => 'customer']);
    });
});