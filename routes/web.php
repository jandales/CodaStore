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
use App\Http\Controllers\InboxController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PlaceOrderController;
use App\Http\Controllers\SocialSiteController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CouponFrontController;
use App\Http\Controllers\SendUsEmailController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\DashboardController;
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
// exception error
Route::get('/server-error', function() {
    return view('server-error');
})->name('server.error');

Route::get('/',[AppController::class, 'index'])->name('home'); 
Route::get('/search',[AppController::class, 'search'])->name('search');
Route::get('/create-cart-cookie', [AppController::class, 'setCartCookie']);
Route::get('/about', [AppController::class, 'about'])->name('about');
Route::get('/contact', [AppController::class, 'contact'])->name('contact');

Route::post('/send-message', [SendUsEmailController::class, 'send'])->name('sendMessage');

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

Route::get('/cart/select/{ShippingMethod:id}/shipping-method/',[CartController::class, 'selectShippingMethod'])->name('cart.select.shippingMethod');
//cart ajax request 
Route::get('/get-user-cart', [CartController::class, 'get_user_cart']);

/*
|--------------------------------------------------------------------------
| End of Cart Controller  Routes
|--------------------------------------------------------------------------
*/




/*
|--------------------------------------------------------------------------
| Admin Controller  Routes
|--------------------------------------------------------------------------
*/



// Shop route

Route::get( '/shop', [ ShopController::class, 'index' ])->name( 'shop' ); 

Route::get( '/shop/{category:slug}', [ ShopController::class, 'category' ])->name( 'shop.category' );

Route::get( '/shop/item/{product:id}', [ ShopController::class, 'view' ])->name( 'shop.product' ); 

Route::get('/shop/sort-by/{value}', [ShopController::class, 'sortBy'])->name('shop.sort');

Route::post( '/shop/search', [ ShopController::class, 'search' ])->name( 'shop.search' ); 

Route::get( '/product/hasvariant/{product:id}', [ ShopController::class, 'hasVariants' ])->name( 'product.hasvariants' ); 


Route::group(['middleware' => 'auth'], function(){

    Route::post('/shop/review/store/{product:id}',[ReviewController::class, 'store'])->name('review.store');

    Route::put('/shop/review/{review:id}/edit/{rate}',[ReviewController::class, 'update'])->name('review.update');

    Route::delete('/shop/review/{review:id}',[ReviewController::class, 'destroy'])->name('review.destroy');


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

    Route::get('/account/profile/password',[UserController::class, 'password'])->name('account.password');

    Route::get('/account/profile/upload/avatar',[UserController::class, 'upload'])->name('account.upload');

    Route::get('/account/profile/edit', [UserController::class, 'edit'])->name('account.edit');
    
    Route::put('/account/profile/change-password', [UserController::class, 'changePassword'])->name('account.changePassword');
   
    // route Shipping address    
    Route::get('/account/shipping-address', [UserShippingAddressController::class, 'index'])->name('account.shippingaddress');

    Route::get('/account/shipping-address/create', [UserShippingAddressController::class, 'create'])->name('account.shippingaddress.create');
    
    Route::post('/account/shipping-address/store', [UserShippingAddressController::class, 'store'])->name('account.shippingaddress.store');

    Route::get('/account/shipping-address/{address:id}/edit', [UserShippingAddressController::class, 'edit'])->name('account.shippingaddress.edit');

    Route::put('/account/shipping-address/{address:id}/update', [UserShippingAddressController::class, 'update'])->name('account.shippingaddress.update');

    Route::delete('/account/shipping-address/{address:id}/destroy', [UserShippingAddressController::class, 'destroy'])->name('account.shippingaddress.destroy');

    Route::put('/account/shipping-address/{address:id}/update-status', [UserShippingAddressController::class, 'set_default_address'])->name('account.shippingaddress.update-status');
    // payment Method
    Route::get('/account/payment-option', [UserPaymentOptionController::class, 'index'])->name('account.payment-option');

    Route::get('/account/payment-option/create', [UserPaymentOptionController::class, 'create'])->name('account.payment-option.create');

    Route::post('/account/payment-option/store', [UserPaymentOptionController::class, 'store'])->name('account.payment-option.store');

    Route::get('/account/payment-option/edit/{option:id}', [UserPaymentOptionController::class, 'edit'])->name('account.payment-option.edit');

    Route::put('/account/payment-option/update/{option:id}', [UserPaymentOptionController::class, 'update'])->name('account.payment-option.update');

    Route::delete('/account/payment-option/destroy/{option:id}', [UserPaymentOptionController::class, 'destroy'])->name('account.payment-option.destroy');

    Route::put('/account/payment-option/status/{option:id}', [UserPaymentOptionController::class, 'updateStatus'])->name('account.payment-option.update.status');   


    
    Route::get('/checkout/information', [CheckOutController::class, 'index' ])->name('checkout.information');

    Route::post('/checkout/information/store', [CheckOutController::class, 'store' ])->name('checkout.information.store');
  
    Route::get('/checkout/shipping', [CheckOutController::class, 'shipping' ])->name('checkout.shipping');

    Route::put('/checkout/shipping/update',[CheckOutController::class, 'updateShippingMethod'])->name('checkout.shipping.update');

    Route::get('/checkout/payment', [CheckOutController::class, 'payment' ])->name('checkout.payment');
    
    Route::get('/checkout/get-shipping-charge/{id}', [CheckOutController::class, 'shippingCharge']);

    Route::post('/checkout/pay-now/store', [PlaceOrderController::class, 'store'])->name('checkout.paynow');    

    Route::get('/checkout/completed', [CheckOutController::class, 'completed' ])->name('checkout.completed');    

    Route::get('/get-shipping-method/{ShippingMethod:id}', [ShippingMethodController::class, 'getShippingMethod']);
 
/*
|--------------------------------------------------------------------------
| orders  Controller  Routes
|--------------------------------------------------------------------------
*/

    
    Route::get('/account/orders/{status}', [OrderController::class, 'index'])->name('account.orders');

    Route::get('/account/orders/detail/{order:id}', [OrderController::class, 'details'])->name('orders.details');

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
Route::group(['middleware' => 'adminLogined', 'prefix' => 'admin'], function(){
    Route::get('/register',[AdminRegisterController::class, 'index'])->name('admin.register');
    Route::post('/register',[AdminRegisterController::class, 'store'])->name('admin.store');
});
/*
|--------------------------------------------------------------------------
| Admin Login Controller  Routes
|--------------------------------------------------------------------------
*/



Route::group(['middleware' => 'guest:admin', 'prefix' => 'admin'], function(){   

    Route::get('',[DashboardController::class, 'index'])->name('admin');

    Route::get('/login',[AdminLoginController::class, 'index'])->name('admin.login');

    Route::post('/login',[AdminLoginController::class, 'store'])->name('admin.login.store');

    Route::get('/forget-password', [AdminResetPasswordController::class, 'index'])->name('admin.forget.password');

    Route::post('/forget-password-request', [AdminResetPasswordController::class, 'request'])->name('admin.request.password');

    Route::get('/reset_password/{token}', [AdminResetPasswordController::class, 'reset']);

    Route::post('/reset_store', [AdminResetPasswordController::class, 'store'])->name('admin.reset.password');

  

   
});

Route::group(['middleware' => 'auth:admin', 'prefix' => 'admin'], function(){  

    Route::post('/logout',[LogoutController::class, 'logout'])->name('admin.logout');

    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.account');

    Route::post('/update/profle/{id}', [AdminController::class, 'update'])->name('update.profile');

    Route::post('/update/password/{id}', [AdminController::class, 'updatePassword'])->name('update.password');

    Route::post('/update/avatar/{id}', [AdminController::class, 'updateAvatar'])->name('update.avatar');
   

});

    

/*
|--------------------------------------------------------------------------
| Dashbaord Routes
|--------------------------------------------------------------------------
*/

Route::post('/users/update',[UserController::class, 'update'])->name('users.update');

Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('password.forgot');
Route::post('/forgot-password', [ForgotPasswordController::class, 'request'])->name('password.request');
Route::get('/reset_password/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('/reset_password', [ResetPasswordController::class, 'reset'])->name('password.store');
Route::get('/send',[ForgotPasswordController::class, 'send'])->name('sent');
Route::post('/upload/avatar',[UserController::class, 'avatar'])->name('upload.avatar');


Route::group(['middleware' => 'auth:admin', 'prefix' => 'admin'], function(){ 

    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('admin.dashboard');


    Route::get('/products', [ProductController::class,  'index'])->name('admin.products');
    Route::get('/products/add',[ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products/store',[ProductController::class, 'store'])->name('admin.products.store');
    Route::put( '/products/update/{product:id}', [ProductController::class, 'update'] )->name('admin.products.update');
    Route::get('/products/edit/{product:id}',[ProductController::class, 'edit'])->name('admin.products.edit');
    Route::delete('/products/destroy/{product:id}',[ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/selected-destroy', [ProductController::class, 'destroySelectedItem'])->name('admin.products.destroys');      
    Route::get('/products/search', [ProductController::class, 'search'])->name('admin.products.search');
    Route::put('/products/{product:id}/status',[ProductController::class, 'changeStatus'])->name('admin.products.status.update');
    Route::post('/products/status/{status}',[ProductController::class, 'changeSelectedItemStatus'])->name('admin.products.status.updates');    
    Route::get('/products/find',[ProductController::class, 'find'])->name('admin.products.find'); 
    Route::get('/products/{filterBy}/{value}',[ProductController::class, 'filter'])->name('admin.products.filter');  
    Route::get('/products/get',[ProductController::class, 'getProduct']);     
   
    //image route
    Route::post('/image/uploads', [ImageController::class, 'uploads']);
    Route::delete('/image/{photo:id}/delete', [ImageController::class, 'unlink']);
    // orders Route
    Route::get('/orders',[AdminOrderController::Class, 'index'])->name('admin.orders');
    Route::get('/orders/{order:id}/show',[AdminOrderController::Class, 'show'])->name('admin.orders.show');
    Route::put('/orders/ship/{order:id}',[AdminOrderController::Class, 'toShip'])->name('admin.orders.shipped');
    Route::put('/orders/update-status/{order:id}',[AdminOrderController::Class, 'updateStatus'])->name('admin.orders.shipped1');
    Route::put('/orders/deliver',[AdminOrderController::Class, 'deliver'])->name('admin.orders.deliver');
    Route::get('/orders/search',[AdminOrderController::Class, 'search'])->name('admin.orders.search');
    Route::get('/orders/{status}',[AdminOrderController::Class, 'listbyStatus'])->name('admin.orders.list');
    Route::delete('/orders/destroy/{order:id}',[AdminOrderController::Class, 'destroy'])->name('admin.orders.destroy');
    
    // Route inventory
    Route::get('/products/inventory', [StockController::class, 'inventory'])->name('admin.inventory');
    Route::get('/products/inventory/filter/{filter}', [StockController::class, 'filter'])->name('admin.inventory.filter');
    Route::get('/products/inventory/adjust/search', [StockController::class, 'search'])->name('admin.inventory.search');
    Route::put('/products/inventory/update/stock/{stock:id}', [StockController::class, 'updateQuantity'])->name('admin.inventory.update.quantity');
    // RouteP
    Route::get('/setting/general',[SettingController::class, 'general'])->name('admin.setting.general');

    Route::get('/setting/campany',[SettingController::class, 'campany'])->name('admin.setting.campany');
    Route::get('/setting/email',[SettingController::class, 'emailer'])->name('admin.setting.emailer');

    Route::put('/setting/general/update/{general_setting}', [SettingController::class, 'updateGeneral'])->name('admin.setting.general.update');
    Route::put('/setting/general/campany/update/{general_setting}', [SettingController::class, 'updateCampanyAddress'])->name('admin.setting.campany.update');
 
    Route::get('/setting/social',[SocialSiteController::class, 'index'])->name('admin.setting.social');
    Route::post('/setting/social/store', [SocialSiteController::class, 'store'])->name('admin.setting.social.store');
    Route::put('/setting/social/update/{site}', [SocialSiteController::class, 'update'])->name('admin.setting.social.update');
    Route::delete('/setting/social/destroy/{site}', [SocialSiteController::class, 'destroy'])->name('admin.setting.social.destroy');
     /*
    |--------------------------------------------------------------------------
    | Coupon Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons');
    route::get('/coupons/create', [CouponController::class, 'create'])->name('admin.coupon.create');
    Route::get('/coupons/edit/{coupon:id}', [CouponController::class, 'edit'])->name('admin.coupon.edit');
    Route::get('/coupons/show/{coupon:id}', [CouponController::class, 'show'])->name('admin.coupon.show');
    Route::post('/coupons/store', [CouponController::class, 'store'])->name('admin.coupon.store');
    Route::put('/coupons/update/{coupon:id}', [CouponController::class, 'update'])->name('admin.coupon.update');
    Route::delete('/coupons/destroy/{coupon:id}', [CouponController::class, 'destroy'])->name('admin.coupon.destroy');
    Route::delete('/coupons/destroy-selected-item', [CouponController::class, 'destroySelectedItem'])->name('admin.coupon.destroy.selected');
    Route::get('/coupons/search', [CouponController::class, 'search'])->name('admin.coupons.search');
     /*
    |--------------------------------------------------------------------------
    | Categories Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/products/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::get('/api/products/categories', [CategoryController::class, 'lists'])->name('admin.categories.all');
    Route::post('/products/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/products/categories/{category:slug}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/products/categories/update/{category:slug}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/products/categories/delete/{category:slug}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::delete('/products/categories/selected/delete/', [CategoryController::class, 'destroySelected'])->name('admin.categories.selected.destroy');
    Route::get('/products/categories/search', [CategoryController::class, 'search'])->name('admin.categories.seacrh');
    /*
    |--------------------------------------------------------------------------
    | Attributes Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/products/attributes', [AttributeController::class, 'index'])->name('admin.attributes');
    Route::post('/products/attributes/store', [AttributeController::class, 'store'])->name('admin.attributes.store');
    Route::get('/products/attributes/edit/{attribute:slug}', [AttributeController::class, 'edit'])->name('admin.attributes.edit');
    Route::put('/products/attributes/update/{attribute:slug}', [AttributeController::class, 'update'])->name('admin.attributes.update');
    Route::delete('/products/attributes/{attribute:slug}/destroy', [AttributeController::class, 'destroy'])->name('admin.attributes.destroy');
    Route::delete('/products/attributes/selected-destroy', [AttributeController::class, 'destroySelected'])->name('admin.attributes.destroy.selected');
    Route::get('/products/attributes/p/search', [AttributeController::class, 'search'])->name('admin.attributes.search');
    
    Route::get('/attributes/all', [AttributeController::class, 'getAll']);
   
  
   /*
    |--------------------------------------------------------------------------
    | Variants Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/variants/store', [VariantController::class, 'store'])->name('variants.store');
    Route::delete('/variants/destroy/{variant:id}', [VariantController::class, 'destroy'])->name('variants.destroy');
     /*
    |--------------------------------------------------------------------------
    | Review admin Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/reviews', [ReviewController::class , 'index'])->name('admin.reviews');
    Route::get('/reviews/block/{status}', [ReviewController::class, 'listbyStatus'])->name('admin.reviews.list');
    Route::put('/reviews/block/{review:id}', [ReviewController::class , 'block'])->name('admin.reviews.block');
    Route::delete('/reviews/destroy/{review:id}', [ReviewController::class , 'destroy'])->name('admin.reviews.destroy');
    Route::delete('/reviews/selected/destroy', [ReviewController::class , 'destroySelected'])->name('admin.reviews.selected.destroy');
    Route::get('/reviews/search', [ReviewController::class , 'search'])->name('admin.reviews.search');


    // Shipping method

    Route::get('/setting/shipping-method/index',[ShippingMethodController::class, 'index'])->name('admin.shipping.method');
    Route::get('/setting/shipping-method/create',[ShippingMethodController::class, 'create'])->name('admin.shipping.method.create');
    Route::post('/setting/shipping-method/store',[ShippingMethodController::class, 'store'])->name('admin.shipping.method.store');
    Route::get('/setting/shipping-method/search',[ShippingMethodController::class, 'search'])->name('admin.shipping.method.search');

    Route::get('/setting/shipping-method/{method:id}/edit',[ShippingMethodController::class, 'edit'])->name('admin.shipping.method.edit');
    Route::put('/setting/shipping-method/{method:id}/update',[ShippingMethodController::class, 'update'])->name('admin.shipping.method.update');
    Route::delete('/setting/shipping-method/{method:id}/destroy',[ShippingMethodController::class, 'destroy'])->name('admin.shipping.method.destroy');
    Route::delete('/setting/shipping-method/selected-destroy',[ShippingMethodController::class, 'selected_destroy'])->name('admin.shipping.method.selected.destroy');
    Route::put('/setting/shipping-method/update-status/{method}/{status}',[ShippingMethodController::class, 'update_status'])->name('admin.shipping.method.update.status');
    Route::put('/setting/shipping-method/selected-update-status',[ShippingMethodController::class, 'selected_update_status'])->name('admin.shipping.method.selected.update.status');

    Route::get('/users', [AdminController::class, 'index'])->name('admin.users');

    Route::get('/users/create', [AdminController::class, 'create'])->name('admin.users.create');

    Route::post('/users/store', [AdminController::class, 'store'])->name('admin.users.store');

    Route::get('/users/edit/{admin:id}', [AdminController::class, 'edit'])->name('admin.users.edit');

    Route::put('/users/update/{admin:id}', [AdminController::class, 'update'])->name('admin.users.update');

    Route::post('/users/destroy/{admin:id}',[AdminController::class, 'destroy'])->name('admin.users.destroy');

    Route::post('/users/selected/destory',[AdminController::class, 'destroySelectedItem'])->name('admin.users.destroySelectedItem');

    Route::put('/users/change-selected-item-role-to/',[AdminController::class, 'updateSelectItemRoleTo'])->name('admin.users.updateSelectItemRoleTo');

    Route::put('/users/change-selected-item-role-to/',[AdminController::class, 'updateSelectItemRoleTo'])->name('admin.users.updateSelectItemRoleTo');

    Route::get('/users/search', [AdminController::class, 'search'])->name('admin.users.search');

    Route::get('/users/show/{admin:id}', [AdminController::class, 'show'])->name('admin.users.show');

    Route::post('/users/send-reset-password/{admin:id}', [AdminController::class, 'sentResetPassword'])->name('admin.users.sentPasswordResetPassword');

    Route::put('/changepassword/{admin:id}',[AdminController::class, 'updatePassword'])->name('users.updatePassword');

    /*
|--------------------------------------------------------------------------
| Customers Controller  Routes
|--------------------------------------------------------------------------
*/

Route::get('/customers', [UserController::class, 'index'])->name('admin.customers');

Route::get('/customers/show/{user:id}', [UserController::class, 'show'])->name('admin.customers.show');

Route::delete('/customers/destroy/{user:id}', [UserController::class, 'destroy'])->name('admin.customers.destroy');

Route::delete('/customers/selected/destroy', [UserController::class, 'selectedDestroy'])->name('admin.customers.selected.destroy');

Route::get('/customers/search',[UserController::class, 'search'])->name('admin.customers.search');

Route::get('/inboxes', [InboxController::class, 'index'])->name('admin.inbox');
Route::get('/inboxes/{id}', [InboxController::class, 'view'])->name('admin.inbox.show');
Route::patch('/inboxes/{id}', [InboxController::class, 'update'])->name('admin.inbox.update');
Route::delete('/inboxes/{id}', [InboxController::class, 'destroy'])->name('admin.inbox.destroy');
Route::patch('/inboxes/unread/{id}', [InboxController::class, 'unread'])->name('admin.inbox.unread');
        
});





























