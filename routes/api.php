<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/v1/products/collection', [ProductController::class, 'index']);

Route::get('/v1/products/collection/{name}/sort-by={sort}', [ProductController::class, 'sort']);

Route::get('/v1/products/collection/{filter}', [ProductController::class, 'index']);

Route::get('/v1/products/featured/limit={limit}', [ProductController::class, 'featured']);

Route::get('/v1/collection', [ProductController::class, 'collection']);

Route::get('/v1/products/{slug}', [ProductController::class, 'show']);

Route::post('/v1/register', [RegisterController::class, 'register']);

Route::post('/v1/login', [LoginController::class, 'login']);



Route::get('/v1/user/{user_id}/address', [UserAddressController::class, 'index']);

Route::get('/v1/user/{user_id}/address/{id}', [UserAddressController::class, 'show']);

Route::post('/v1/user/{user_id}/address', [UserAddressController::class, 'store']);

Route::put('/v1/user/{user_id}address/{id}', [UserAddressController::class, 'update']);

Route::patch('/v1/user/{user_id}/address/{d}', [UserAddressController::class, 'setActive']);

Route::delete('/v1/user/{$user_id}/address/{id}', [UserAddressController::class, 'destroy']);



Route::get('/v1/user/{user_id}/paymentOption', [UserAddressController::class, 'index']);

Route::get('/v1/user/{user_id}/paymentOption/{id}', [UserAddressController::class, 'show']);

Route::post('/v1/user/{user_id}/paymentOption', [UserAddressController::class, 'store']);

Route::put('/v1/user/{user_id}/paymentOption/{id}', [UserAddressController::class, 'update']);

Route::patch('/v1/user/{user_id}/paymentOption/{id}', [UserAddressController::class, 'setActive']);

Route::delete('/v1/user/{user_id}/paymentOption/{id}', [UserAddressController::class, 'destroy']);











