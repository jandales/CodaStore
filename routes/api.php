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



Route::post('/register', [RegisterController::class, 'register']);

Route::post('/login', [LoginController::class, 'login']);

Route::controller(CartController::class)->group(function () { 

        Route::post('/cart/add','store');

        Route::post('/set-cart-cookie','setCookie');

        Route::get('/cart','index');

        Route::patch('/cart/{id}','update');

        Route::delete('/cart/item/{id}', 'destroy');

        Route::get('/cart/count','count');

});

Route::controller(ShippingMethodController::class)->group(function () {        

     Route::get('/shipping-methods', 'index');

     Route::get('/shipping-methods/{id}',  'show');

});


Route::controller(ProductController::class)->group(function () {

        Route::get('/products/collection', 'index');

        Route::get('/products/collection/{name}/sort-by={sort}','sort');

        Route::get('/products/collection/{filter}','index');

        Route::get('/products/featured/limit={limit}',  'featured');

        Route::post('/products/search',  'search');

        Route::get('/collection', 'collection');

        Route::get('/products/{slug}',  'show');
});

Route::group(['middleware' => ['auth:sanctum']], function ()  {
       
        Route::controller(UserController::class)->group(function (){

                Route::get('/user',  'user');

                Route::put('/user',  'update');
        
                Route::post('/user/upload',  'upload');
        
                Route::delete('/user/remove-image',  'removeImage');
        });

        Route::controller(UserAddressController::class)->group(function (){

                Route::get('/user/address',  'index');

                Route::get('/user/address/{id}', 'show');

                Route::get('/user/address/default/1', 'default');        

                Route::post('/user/address',  'store');

                Route::put('/user/address/{id}', 'update');

                Route::patch('/user/address/{id}', 'setActive');

                Route::delete('/user/address/{id}',  'destroy');

        });

        Route::controller(UserPaymentOptionController::class)->group(function () {
                
                Route::get('/user/paymentOption', 'index');

                Route::get('/user/paymentOption/{id}',  'show');

                Route::get('/user/paymentOption/default/1',  'default');

                Route::get('/user/paymentOption/card-number/{card_number}',  'findByCardNumber');

                Route::post('/user/paymentOption',  'store');

                Route::put('/user/paymentOption/{id}',  'update');

                Route::patch('/user/paymentOption/{id}',  'setActive');

                Route::delete('/user/paymentOption/{id}',  'destroy');

        });

        Route::controller(OrderController::class)->group(function () {
                
                Route::get('/user/orders','index');

                Route::get('/user/orders/{id}', 'show');  

                Route::patch('/user/orders/cancel/{id}', 'cancel');  

        });

        Route::controller(CheckOutController::class)->group(function () {              

                Route::get('/checkout', 'index');
        
                Route::post('/checkout', 'store');
        
                Route::get('/checkout/shipping', 'shipping');
        
                Route::put('/checkout/shipping/update', 'updateShippingMethod');

        });

        Route::controller(CartController::class)->group(function () { 

                Route::get('/cart/coupon',  'getCoupon');

                Route::post('/cart/apply-coupon', 'couponActivate');
        
                Route::delete('/cart/remove-coupon',  'couponRemove');
        
                Route::post('/contact/send-message',  'sendMessage');

        });

        Route::delete('/user/logout',[LogoutController::class, 'logout']);       

        Route::post('/checkout/placeorder',[PlaceOrderController::class, 'store']);     
        
});




