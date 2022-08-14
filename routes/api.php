<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\PlaceOrderController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ShippingMethodController;
use App\Http\Controllers\Api\UserPaymentOptionController;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });




Route::get('/products/collection', [ProductController::class, 'index']);

Route::get('/products/collection/{name}/sort-by={sort}', [ProductController::class, 'sort']);

Route::get('/products/collection/{filter}', [ProductController::class, 'index']);

Route::get('/products/featured/limit={limit}', [ProductController::class, 'featured']);

Route::post('/products/search', [ProductController::class, 'search']);

Route::get('/collection', [ProductController::class, 'collection']);

Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::post('/register', [RegisterController::class, 'register']);

Route::post('/login', [LoginController::class, 'login']);



Route::group(['middleware' => ['auth:sanctum']], function ()  {

       

        Route::get('/user', [UserController::class, 'user']);

        Route::put('/user', [UserController::class, 'update']);

        Route::post('/user/upload', [UserController::class, 'upload']);

        Route::delete('/user/remove-image', [UserController::class, 'removeImage']);

        Route::get('/user/address', [UserAddressController::class, 'index']);

        Route::get('/user/address/{id}', [UserAddressController::class, 'show']);

        Route::get('/user/address/default/1', [UserAddressController::class, 'default']);        

        Route::post('/user/address', [UserAddressController::class, 'store']);

        Route::put('/user/address/{id}', [UserAddressController::class, 'update']);

        Route::patch('/user/address/{id}', [UserAddressController::class, 'setActive']);

        Route::delete('/user/address/{id}', [UserAddressController::class, 'destroy']);

        Route::get('/user/paymentOption', [UserPaymentOptionController::class, 'index']);

        Route::get('/user/paymentOption/{id}', [UserPaymentOptionController::class, 'show']);

        Route::get('/user/paymentOption/default/1', [UserPaymentOptionController::class, 'default']);

        Route::get('/user/paymentOption/card-number/{card_number}', [UserPaymentOptionController::class, 'findByCardNumber']);

        Route::post('/user/paymentOption', [UserPaymentOptionController::class, 'store']);

        Route::put('/user/paymentOption/{id}', [UserPaymentOptionController::class, 'update']);

        Route::patch('/user/paymentOption/{id}', [UserPaymentOptionController::class, 'setActive']);

        Route::delete('/user/paymentOption/{id}', [UserPaymentOptionController::class, 'destroy']);

        Route::get('/user/orders',[OrderController::class, 'index']);

        Route::get('/user/orders/{id}',[OrderController::class, 'show']);  

        Route::patch('/user/orders/cancel/{id}',[OrderController::class, 'cancel']);  

        Route::delete('/user/logout',[LogoutController::class, 'logout']);

        Route::get('/checkout',[CheckOutController::class, 'index']);

        Route::post('/checkout',[CheckOutController::class, 'store']);

        Route::get('/checkout/shipping',[CheckOutController::class, 'shipping']);

        Route::put('/checkout/shipping/update',[CheckOutController::class, 'updateShippingMethod']);

        Route::post('/checkout/placeorder',[PlaceOrderController::class, 'store']);

        Route::get('/cart/coupon', [CartController::class, 'getCoupon']);

        Route::post('/cart/apply-coupon', [CartController::class, 'couponActivate']);

        Route::delete('/cart/remove-coupon', [CartController::class, 'couponRemove']);

        Route::post('/contact/send-message', [ContactController::class, 'sendMessage']);
        
});

Route::post('/cart/add', [CartController::class, 'store']);

Route::post('/set-cart-cookie', [CartController::class, 'setCookie']);

Route::get('/cart', [CartController::class, 'index']);

Route::patch('/cart/{id}', [CartController::class, 'update']);

Route::delete('/cart/item/{id}',[CartController::class, 'destroy']);

Route::get('/cart/count', [CartController::class, 'count']);

Route::get('/shipping-methods', [ShippingMethodController::class, 'index']);

Route::get('/shipping-methods/{id}', [ShippingMethodController::class, 'show']);

