<?php


use Illuminate\Http\Request;


use App\Mail\forgorPasswordMail;


use Illuminate\Support\Facades\Crypt;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceOrderController;
use App\Http\Controllers\SocialSiteController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CouponFrontController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\UserPaymentOptionController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Auth\User\RegisterController;
use App\Http\Controllers\Auth\User\UserLoginController;
use App\Http\Controllers\UserShippingAddressController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Auth\AdminResetPasswordController;
use App\Http\Controllers\Auth\User\ResetPasswordController;
use App\Http\Controllers\Auth\User\ForgotPasswordController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',[AppController::class, 'index'])->name('home'); 
Route::get('/search',[AppController::class, 'search'])->name('search');
Route::get('/create-cart-cookie', [AppController::class, 'setCartCookie']);
Route::get('/about', [AppController::class, 'about'])->name('about');
Route::get('/contact', [AppController::class, 'contact'])->name('contact');

Route::post('/send-message', [AppController::class, 'sendMessage'])->name('sendMessage');

/*
|--------------------------------------------------------------------------
| Cart Controller  Routes
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class , 'index'])->name('cart');

Route::post('/cart/store/{product:id}', [CartController::class, 'store'])->name('cart.store');

Route::delete('/cart/destroy/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::delete('/cart/destroy', [CartController::class, 'destroies'])->name('cart.destroy.selected');

Route::put('/cart/update/{cartitem:id}', [CartController::class, 'update'])->name('cart.update');

Route::put('/cart/product/discount/update', [CartController::class , 'updateProductsDiscount' ]);

Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');    

Route::get('/cart/coupon/activate', [CartController::class , 'couponActivate'])->name('coupon');

Route::put('/cart/coupon/remove', [CartController::class, 'couponRemove'])->name('coupon.remove');

Route::get('/cart/select/{id}/shipping-method/',[CartController::class, 'selectShippingMethod'])->name('cart.select.shippingMethod');
//cart ajax request 
Route::get('/get-user-cart', [CartController::class, 'get_user_cart']);

/*
|--------------------------------------------------------------------------
| End of Cart Controller  Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Customers Controller  Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/customers', [UserController::class, 'index'])->name('admin.customers');

Route::get('/admin/customers/{id}/show', [UserController::class, 'show'])->name('admin.customers.show');

Route::delete('/admin/users/delete', [UserController::class, 'delete'])->name('admin.users.delete');

/*
|--------------------------------------------------------------------------
| Admin Controller  Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users');

Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.users.create');

Route::post('/admin/users/store', [AdminController::class, 'store'])->name('admin.users.store');

Route::get('/admin/users/{admin}/edit/', [AdminController::class, 'edit'])->name('admin.users.edit');

Route::put('/admin/users/{admin}/update/', [AdminController::class, 'update'])->name('admin.users.update');

Route::delete('/admin/users/{admin:id}/destroy',[AdminController::class, 'destroy'])->name('admin.users.destroy');

Route::delete('/admin/users/destroy',[AdminController::class, 'destroySelectedItem'])->name('admin.users.destroySelectedItem');

Route::put('/admin/users/change-selected-item-role-to/',[AdminController::class, 'updateSelectItemRoleTo'])->name('admin.users.updateSelectItemRoleTo');

Route::put('/admin/users/change-selected-item-role-to/',[AdminController::class, 'updateSelectItemRoleTo'])->name('admin.users.updateSelectItemRoleTo');

Route::post('/admin/users/search', [AdminController::class, 'search'])->name('admin.users.search');

Route::get('/admin/users/show/{admin:id}', [AdminController::class, 'show'])->name('admin.users.show');

Route::post('/admin/users/{admin}/send-reset-password/', [AdminController::class, 'sentResetPassword'])->name('admin.users.sentPasswordResetPassword');

Route::put('/admin/changepassword/{admin:id}',[AdminController::class, 'updatePassword'])->name('users.updatePassword');


// Shop route

Route::get( '/shop', [ ShopController::class, 'index' ])->name( 'shop' ); 

Route::get( '/shop/{category}', [ ShopController::class, 'category' ])->name( 'shop.category' );

Route::get( '/shop/item/{product}', [ ShopController::class, 'view' ])->name( 'shop.product' ); 

Route::get('/shop/sort-by/{value}', [ShopController::class, 'sortBy'])->name('shop.sort');

Route::post( '/shop/search', [ ShopController::class, 'search' ])->name( 'shop.search' ); 

Route::get( '/shop/filter/price/{prices}', [ ShopController::class, 'filterByPrice' ])->name( 'shop.filter.price' ); 

Route::get( '/shop/filter/color/{attribute}', [ ShopController::class, 'filterByAttribute' ])->name( 'shop.filter.attribute' ); 

Route::get( '/shop/filter/tags/{tag}', [ ShopController::class, 'filterByTags' ])->name( 'shop.filter.tags' ); 

Route::get( '/shop/filter/price/desc/{price}', [ ShopController::class, 'filterPriceDesc' ])->name( 'shop.filter.price.order' ); 

Route::get( '/shop/filter/latest', [ ShopController::class, 'filterLatest' ])->name( 'shop.filter.latest' ); 

Route::get( '/product/hasvariant/{product:id}', [ ShopController::class, 'hasVariants' ])->name( 'product.hasvariants' ); 


Route::group(['middleware' => 'auth'], function(){

    Route::post('/shop/review/{product}',[ReviewController::class, 'store'])->name('review.store');

    Route::put('/shop/review/{review}/edit/{rate}',[ReviewController::class, 'update'])->name('review.update');

    Route::delete('/shop/review/{review}',[ReviewController::class, 'destroy'])->name('review.destroy');


});






/*
|--------------------------------------------------------------------------
| Users Register Controller  Routes
|--------------------------------------------------------------------------
*/

Route::get('/login',[UserLoginController::class, 'index'])->name('login');

Route::post('/login',[UserLoginController::class, 'login'])->name('login.store');

Route::group(['middleware' => 'auth'], function(){

    Route::get('/account',[UserController::class, 'account'])->name('account');

    Route::get('/account/password',[UserController::class, 'password'])->name('account.password');

    Route::get('/account/upload/avatar',[UserController::class, 'upload'])->name('account.upload');

    Route::get('/account/edit/profile', [UserController::class, 'edit'])->name('account.edit');
    Route::put('/account/change-password', [UserController::class, 'changePassword'])->name('account.changePassword');
   
    // route Shipping address    
    Route::get('/account/shipping-address', [UserShippingAddressController::class, 'index'])->name('account.shippingaddress');

    Route::get('/account/shipping-address/create', [UserShippingAddressController::class, 'create'])->name('account.shippingaddress.create');
    
    Route::post('/account/shipping-address/store', [UserShippingAddressController::class, 'store'])->name('account.shippingaddress.store');

    Route::get('/account/shipping-address/{address}/edit', [UserShippingAddressController::class, 'edit'])->name('account.shippingaddress.edit');

    Route::put('/account/shipping-address/{address}/update', [UserShippingAddressController::class, 'update'])->name('account.shippingaddress.update');

    Route::delete('/account/shipping-address/{address}/destroy', [UserShippingAddressController::class, 'destroy'])->name('account.shippingaddress.destroy');

    Route::put('/account/shipping-address/{address}/update-status', [UserShippingAddressController::class, 'set_default_address'])->name('account.shippingaddress.update-status');
    // payment Method
    Route::get('/account/payment-option', [UserPaymentOptionController::class, 'index'])->name('account.payment-option');

    Route::get('/account/payment-option/create', [UserPaymentOptionController::class, 'create'])->name('account.payment-option.create');

    Route::post('/account/payment-option/store', [UserPaymentOptionController::class, 'store'])->name('account.payment-option.store');

    Route::get('/account/payment-option/edit/{option}', [UserPaymentOptionController::class, 'edit'])->name('account.payment-option.edit');

    Route::put('/account/payment-option/update/{option}', [UserPaymentOptionController::class, 'update'])->name('account.payment-option.update');

    Route::delete('/account/payment-option/destroy/{option}', [UserPaymentOptionController::class, 'destroy'])->name('account.payment-option.destroy');

    Route::put('/account/payment-option/status/{option}', [UserPaymentOptionController::class, 'updateStatus'])->name('account.payment-option.update.status');   

    Route::get('/checkout/information', [CheckOutController::class, 'index' ])->name('checkout.information');

    Route::post('/checkout/information/store', [CheckOutController::class, 'store' ])->name('checkout.information.store');
  
    Route::get('/checkout/shipping', [CheckOutController::class, 'shipping' ])->name('checkout.shipping');

    Route::put('/checkout/shipping/update',[CheckOutController::class, 'updateShippingMethod'])->name('checkout.shipping.update');

    Route::get('/checkout/payment', [CheckOutController::class, 'payment' ])->name('checkout.payment');
    
    Route::get('/checkout/get-shipping-charge/{id}', [CheckOutController::class, 'shippingCharge']);

    Route::post('/checkout/pay-now/store', [PlaceOrderController::class, 'store'])->name('checkout.paynow');    

    Route::get('/checkout/completed', [CheckOutController::class, 'completed' ])->name('checkout.completed');    

    Route::get('/get-shipping-method/{id}', [ShippingMethodController::class, 'getShippingMethod']);
 
/*
|--------------------------------------------------------------------------
| orders  Controller  Routes
|--------------------------------------------------------------------------
*/

    
    Route::get('/account/orders/{status}', [OrderController::class, 'index'])->name('account.orders');

    Route::get('/account/orders/detail/{order}', [OrderController::class, 'details'])->name('orders.details');

    Route::get('/account/orders/{product}/review', [OrderController::class, 'review'])->name('orders.review');

    Route::post('/order/store', [OrderController::class, 'store'])->name('orders.store');

    Route::put('/order/cancel/{order}',[OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/checkout/order-details/{order}', [CheckOutController::class, 'details'])->name('checkout.details');  


});

/*
|--------------------------------------------------------------------------
| User/Accout Dashbaord  Controller  Routes
|--------------------------------------------------------------------------
*/


// Route::get('/account/dashboard/', [DashboardController::class, 'index'])->name('account.dashboard');





/*
|--------------------------------------------------------------------------
| users Register Controller  Routes
|--------------------------------------------------------------------------
*/

Route::post('/register',[RegisterController::class, 'store'])->name('register.store');

Route::get('/register', [RegisterController::class, 'index'])->name('register');

/*
|--------------------------------------------------------------------------
| users logout Controller  Routes
|--------------------------------------------------------------------------
*/

Route::get('/logout',[LogoutController::class, 'store'])->name('logout');



Route::get('/variant/{id}', [VariantController::class, 'variant'])->name('variant');















/*
|--------------------------------------------------------------------------
| Admin Register Controller  Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'adminLogined'], function(){
    Route::get('/admin/register',[AdminRegisterController::class, 'index'])->name('admin.register');
    Route::post('/admin/register',[AdminRegisterController::class, 'store'])->name('admin.store');
});
/*
|--------------------------------------------------------------------------
| Admin Login Controller  Routes
|--------------------------------------------------------------------------
*/



Route::group(['middleware' => 'guest:admin'], function(){   

    Route::get('/admin',[DashboardController::class, 'index'])->name('admin');

    Route::get('/admin/login',[AdminLoginController::class, 'index'])->name('admin.login');

    Route::post('/admin/login',[AdminLoginController::class, 'store'])->name('admin.login.store');

    Route::get('/admin/forget-password', [AdminResetPasswordController::class, 'index'])->name('admin-forget-password');

    Route::post('/admin/forget-password-request', [AdminResetPasswordController::class, 'request'])->name('admin-request-password');

    Route::get('/admin/reset_password/{token}', [AdminResetPasswordController::class, 'reset']);

    Route::post('/admin/reset_store', [AdminResetPasswordController::class, 'store'])->name('admin-reset-password');

  

   
});

Route::group(['middleware' => 'auth:admin'], function(){  

    Route::post('/admin/logout',[LogoutController::class, 'logout'])->name('admin.logout');

    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.account');

    Route::post('/admin/update/profle/{id}', [AdminController::class, 'update'])->name('update.profile');

    Route::post('/admin/update/password/{id}', [AdminController::class, 'updatePassword'])->name('update.password');

    Route::post('/admin/update/avatar/{id}', [AdminController::class, 'updateAvatar'])->name('update.avatar');
   

});

    

/*
|--------------------------------------------------------------------------
| Dashbaord Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth:admin'], function () {
  
    Route::get('/admin/dashboard',[DashboardController::class,'dashboard'])->name('admin.dashboard');

    Route::get('/admin/coupons', [CouponController::class, 'index'])->name('admin.coupons');

    route::get('/admin/coupons/create', [CouponController::class, 'create'])->name('admin.coupon.create');

    Route::get('/admin/coupons/edit/{coupon}', [CouponController::class, 'edit'])->name('admin.coupon.edit');

    Route::get('/admin/coupons/detail/{coupon}', [CouponController::class, 'show'])->name('admin.coupon.show');

    Route::post('/admin/coupons/store', [CouponController::class, 'store'])->name('admin.coupon.store');

    Route::put('/admin/coupons/update/{coupon}', [CouponController::class, 'update'])->name('admin.coupon.update');

    Route::delete('/admin/coupons/destroy/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupon.destroy');

    Route::delete('/admin/coupons/destroy-selected-item', [CouponController::class, 'destroySelectedItem'])->name('admin.coupon.destroy.selected');

    Route::post('/admin/coupons/search', [CouponController::class, 'search'])->name('admin.coupons.search');

    Route::get('/admin/product/searchByajax/{input}',  [ProductController::class, 'searchByajax']);

});


Route::post('/users/update',[UserController::class, 'update'])->name('users.update');

Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('password.forgot');
Route::post('/forgot-password', [ForgotPasswordController::class, 'request'])->name('password.request');
Route::get('/reset_password/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('/reset_password', [ResetPasswordController::class, 'reset'])->name('password.store');
Route::get('/send',[ForgotPasswordController::class, 'send'])->name('sent');
Route::post('/upload/avatar',[UserController::class, 'avatar'])->name('upload.avatar');


Route::group(['middleware' => 'auth:admin'], function(){ 
    Route::get('/admin/products', [ProductController::class,  'index'])->name('admin.products');
    Route::get('/admin/products/add',[ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/admin/products/store',[ProductController::class, 'store'])->name('admin.products.store');
    Route::put( '/admin/products/{product}/update', [ProductController::class, 'update'] )->name('admin.products.update');
    Route::get('/admin/products/{product}/edit',[ProductController::class, 'edit'])->name('admin.products.edit');
    Route::delete('/admin/products/destroy/{product}',[ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/admin/products/delete', [ProductController::class, 'delete'])->name('admin.products.destroys');      
    Route::get('/admin/products/search', [ProductController::class, 'search'])->name('admin.products.search');
    Route::put('/admin/products/{product}/status',[ProductController::class, 'changeStatus'])->name('admin.products.status.update');
    Route::post('/admin/products/status/{status}',[ProductController::class, 'changeSelectedItemStatus'])->name('admin.products.status.updates');    
    Route::get('/admin/products/find',[ProductController::class, 'find'])->name('admin.products.find'); 
    Route::get('/admin/products/filter={filter}',[ProductController::class, 'filter'])->name('admin.products.filter');    
   
   
    //image route
    Route::post('/admin/image/uploads', [ImageController::class, 'uploads']);
    Route::delete('/admin/image/{photo:id}/delete', [ImageController::class, 'unlink']);
    // orders Route
    Route::get('/admin/orders',[AdminOrderController::Class, 'index'])->name('admin.orders');
    Route::get('/admin/orders/{order:id}/show',[AdminOrderController::Class, 'show'])->name('admin.orders.show');
    Route::put('/admin/orders/{order:id}/ship',[AdminOrderController::Class, 'toShip'])->name('admin.orders.shipped');
    Route::put('/admin/orders/deliver',[AdminOrderController::Class, 'deliver'])->name('admin.orders.deliver');
    Route::get('/admin/orders/search',[AdminOrderController::Class, 'search'])->name('admin.orders.search');
    Route::get('/admin/orders/{status}',[AdminOrderController::Class, 'listbyStatus'])->name('admin.orders.list');
    
    // Route inventory
    Route::get('/admin/products/inventory', [StockController::class, 'inventory'])->name('admin.inventory');
    Route::get('/admin/products/inventory/filter/{filter}', [StockController::class, 'filter'])->name('admin.inventory.filter');
    Route::get('/admin/products/inventory/search', [StockController::class, 'search'])->name('admin.inventory.search');
    Route::put('/admin/products/inventory/update/stock/{stock:id}', [StockController::class, 'updateQuantity'])->name('admin.inventory.update.quantity');
    // RouteP
    Route::get('/admin/setting/general',[SettingController::class, 'general'])->name('admin.setting.general');

    Route::get('/admin/setting/campany',[SettingController::class, 'campany'])->name('admin.setting.campany');
    Route::get('/admin/setting/email',[SettingController::class, 'emailer'])->name('admin.setting.emailer');

    Route::put('/admin/setting/general/update/{general_setting}', [SettingController::class, 'updateGeneral'])->name('admin.setting.general.update');
    Route::put('/admin/setting/general/campany/update/{general_setting}', [SettingController::class, 'updateCampanyAddress'])->name('admin.setting.campany.update');
 
    Route::get('/admin/setting/social',[SocialSiteController::class, 'index'])->name('admin.setting.social');
    Route::post('/admin/setting/social/store', [SocialSiteController::class, 'store'])->name('admin.setting.social.store');
    Route::put('/admin/setting/social/update/{site}', [SocialSiteController::class, 'update'])->name('admin.setting.social.update');
    Route::delete('/admin/setting/social/destroy/{site}', [SocialSiteController::class, 'destroy'])->name('admin.setting.social.destroy');

     

        
});















Route::group(['middleware' => 'auth:admin'], function(){ 


    /*
    |--------------------------------------------------------------------------
    | Categories Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/products/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::get('/api/admin/products/categories', [CategoryController::class, 'lists'])->name('admin.categories.all');
    Route::post('/admin/products/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/products/categories/{category:id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/admin/products/categories/update/{category:id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/products/categories/delete/{category:id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/admin/products/categories/search', [CategoryController::class, 'search'])->name('admin.categories.seacrh');
    /*
    |--------------------------------------------------------------------------
    | Attributes Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/products/attributes', [AttributeController::class, 'index'])->name('admin.attributes');
    Route::get('/admin/attributes/all', [AttributeController::class, 'getAttributes'])->name('admin.attributes.all'); 
    Route::post('/admin/attributes/store', [AttributeController::class, 'store']);
    Route::get('/admin/attributes/{attribute:id}', [AttributeController::class, 'edit'])->name('admin.attributes.edit');
    Route::put('/admin/attributes/update/{attribute:id}', [AttributeController::class, 'update'])->name('admin.attributes.update');
    Route::delete('/admin/attributes/{attribute:id}/destroy', [AttributeController::class, 'destroy'])->name('admin.attributes.delete');
    route::get('/admin/attributes/variants/{attribute:id}',[AttributeController::class, 'variants'])->name('admin.attributes.variants');    
  
   /*
    |--------------------------------------------------------------------------
    | Variants Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/variants/store', [VariantController::class, 'store'])->name('variants.store');
    Route::delete('/admin/variants/destroy/{variant:id}', [VariantController::class, 'destroy'])->name('variants.destroy');
    /*
    |--------------------------------------------------------------------------
    | stock Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/admin/products/stock/{product}/create', [StockController::class, 'create'])->name('stock.create');
    // Route::post('/admin/products/stock/{stock}/store', [StockController::class, 'store'])->name('stock.store');
    Route::get('/admin/products/stock/{stock}/edit', [StockController::class, 'edit'])->name('stock.edit');
    Route::post('/admin/products/stock/{stock}/update', [StockController::class, 'update'])->name('stock.update');
    /*
    |--------------------------------------------------------------------------
    | Price Routes
    |--------------------------------------------------------------------------
    */
    // Route::get('/admin/products/price/{product}/create', [PriceController::class , 'create'])->name('price.create');
    // Route::post('/admin/products/price/{price}/store', [PriceController::class , 'store'])->name('price.store');
    Route::get('/admin/products/price/{product}/edit', [PriceController::class , 'edit'])->name('price.edit');
    Route::post('/admin/products/price/{product}/update', [PriceController::class , 'update'])->name('price.update');
     /*
    |--------------------------------------------------------------------------
    | Review admin Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/reviews', [ReviewController::class , 'index'])->name('admin.reviews');
    Route::get('/admin/reviews/block/{status}', [ReviewController::class, 'listbyStatus'])->name('admin.reviews.list');
    Route::put('/admin/reviews/block/{review}', [ReviewController::class , 'block'])->name('admin.reviews.block');
    Route::delete('/admin/reviews/delete/{review}', [ReviewController::class , 'delete'])->name('admin.reviews.destroy');
    Route::post('/admin/search/', [ReviewController::class , 'search'])->name('admin.reviews.search');


    // Shipping method

    Route::get('/admin/setting/shipping-method/index',[ShippingMethodController::class, 'index'])->name('admin.shipping.method');

    Route::get('/admin/setting/shipping-method/create',[ShippingMethodController::class, 'create'])->name('admin.shipping.method.create');

    Route::post('/admin/setting/shipping-method/store',[ShippingMethodController::class, 'store'])->name('admin.shipping.method.store');

    Route::get('/admin/setting/shipping-method/{method}/edit',[ShippingMethodController::class, 'edit'])->name('admin.shipping.method.edit');

    Route::put('/admin/setting/shipping-method/{method}/update',[ShippingMethodController::class, 'update'])->name('admin.shipping.method.update');

    Route::delete('/admin/setting/shipping-method/{method}/destroy',[ShippingMethodController::class, 'destroy'])->name('admin.shipping.method.destroy');

    Route::delete('/admin/setting/shipping-method/selected-destroy',[ShippingMethodController::class, 'selected_destroy'])->name('admin.shipping.method.selected.destroy');

    Route::put('/admin/setting/shipping-method/update-status/{method}/{status}',[ShippingMethodController::class, 'update_status'])->name('admin.shipping.method.update.status');

    Route::put('/admin/setting/shipping-method/selected-update-status',[ShippingMethodController::class, 'selected_update_status'])->name('admin.shipping.method.selected.update.status');



});












