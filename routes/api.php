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
use App\Http\Controllers\Api\Admin\DashboardController;

use App\Http\Controllers\Api\Customer\LoginController as CustomerLoginController;
use App\Http\Controllers\Api\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Api\Customer\RegisterController;
use App\Http\Controllers\Api\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Api\Customer\ReviewController;

use App\Http\Controllers\Api\Web\RajaOngkirController;
use App\Http\Controllers\Api\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Api\Web\ProductController as WebProductController;
use App\Http\Controllers\Api\Web\SliderController as WebSliderController;
use App\Http\Controllers\Api\Web\CartController;

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
        Route::GET('/dashboard', [DashboardController::class, 'index', ['as' => 'admin']]);
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

        // route reviews
        Route::POST('/review', [ReviewController::class, 'store'], ['as' => 'customer']);

        // route dashboard
        Route::GET('/dashboard', [CustomerDashboardController::class, 'index', ['as' => 'customer']]);
    });
});

Route::prefix('web')->group(function () {

    // route category
    Route::resource('/categories', WebCategoryController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy'], 'as' => 'web']);

    // route product
    Route::resource('/products', WebProductController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy'], 'as' => 'web']);

    // route slider
    Route::resource('/sliders', WebSliderController::class, ['only' => ['index'], 'as' => 'web']);

    // route rajaongkir
    Route::GET('/rajaongkir/provinces', [RajaOngkirController::class, 'getProvinces'], ['as' => 'web']);

    Route::POST('/rajaongkir/cities', [RajaOngkirController::class, 'getCities'], ['as' => 'web']);

    Route::POST('/rajaongkir/checkOngkir', [RajaOngkirController::class, 'checkOngkir'], ['as' => 'web']);

    Route::group(['middleware' => 'auth:api_customer'], function () {
        
        // GET Cart
        Route::GET('/carts', [CartController::class, 'index'], ['as' => 'web']);

        // POST Cart
        Route::POST('/carts', [CartController::class, 'store'], ['as' => 'web']);

        // GET Cart price
        Route::GET('/carts/total_price', [CartController::class, 'getCartPrice'], ['as' => 'web']);

        // GET Cart weight
        Route::GET('/carts/total_weight', [CartController::class, 'getCartWeight'], ['as' => 'web']);

        // REMOVE Cart
        ROUTE::POST('/carts/remove', [CartController::class, 'removeCart'], ['as' => 'web']);
    });
});