<?php


use Illuminate\Http\Request;

use App\Models\ShippingAddress;
use App\Mail\forgorPasswordMail;


use Illuminate\Support\Facades\Route;


use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;



use App\Http\Controllers\SettingController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WishListController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceOrderController;
use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\PriceController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CouponFrontController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\DirectCheckoutController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Auth\AdminRegisterController;
use App\Http\Controllers\Auth\User\RegisterController;
use App\Http\Controllers\Auth\User\UserLoginController;
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
Route::get('/',[HomeController::class, 'index'])->name('home'); 



Route::get('/about', function (){
    return view('about');
});

Route::get('/contact', function (){
    return view('contact');
});

Route::get('/test', function (){
    return view('admin.products.create1');
});

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

Route::put('/users/changepassword/{admin:id}',[AdminController::class, 'updatePassword'])->name('users.updatePassword');


// Shop route

Route::get( '/shop', [ ShopController::class, 'index' ])->name( 'shop' ); 

Route::get( '/shop/{category}', [ ShopController::class, 'category' ])->name( 'shop.category' );

Route::get( '/shop/item/{product}', [ ShopController::class, 'view' ])->name( 'shop.product' ); 

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


Route::group(['middleware' => 'auth'], function(){

    Route::post('/checkout/{product}',[DirectCheckoutController::class, 'index'])->name('checkout.direct');  


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

    Route::get('/user/addressbook', [UserController::class, 'addressbook']);

    Route::get('/account/edit/profile', [UserController::class, 'edit'])->name('account.edit');

    // address book

    Route::get('/account/addressbook', [AddressBookController::class, 'index'])->name('account.addressbook');

    Route::get('/account/addressbook/create',[AddressBookController::class, 'create'])->name('addressbook.create');

    Route::post('/account/addressbook/store', [ AddressBookController::class, 'store'])->name('addressbook.store');

    Route::get('/account/addressbook/edit/{addressbook}', [ AddressBookController::class, 'edit' ])->name('addressbook.edit');

    Route::put('/account/addressbook/update/{addressbook}', [ AddressBookController::class, 'update' ])->name('addressbook.update');

    Route::put('/account/addressbook/default/{addressbook}', [ AddressBookController::class, 'default' ])->name('addressbook.default');
    
    Route::delete('/account/addressbook/delete/{addressbook}', [ AddressBookController::class, 'destroy' ])->name('addressbook.destroy');

    Route::put('/addressbook/set/{addressbook:id}', [ AddressBookController::class, 'ajaxSetAddress' ])->name('addressbook.set');
    // route Shipping address    
    Route::get('/account/shipping-address', [ShippingAddressController::class, 'index'])->name('account.shippingaddress');

    Route::get('/account/shipping-address/create', [ShippingAddressController::class, 'create'])->name('account.shippingaddress.create');
    
    Route::post('/account/shipping-address/store', [ShippingAddressController::class, 'store'])->name('account.shippingaddress.store');

    Route::get('/account/shipping-address/{address}/edit', [ShippingAddressController::class, 'edit'])->name('account.shippingaddress.edit');

    Route::put('/account/shipping-address/{address}/update', [ShippingAddressController::class, 'update'])->name('account.shippingaddress.update');

    Route::delete('/account/shipping-address/{address}/destroy', [ShippingAddressController::class, 'destroy'])->name('account.shippingaddress.destroy');

    Route::put('/account/shipping-address/{address}/update-status', [ShippingAddressController::class, 'set_default_address'])->name('account.shippingaddress.update-status');
    // payment Method


 

    /*
    |--------------------------------------------------------------------------
    | Cart Controller  Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/cart', [CartController::class , 'index'])->name('cart');

    Route::post('/cart/store/{product:id}', [CartController::class, 'store'])->name('cart.store');

    Route::delete('/cart/destroy/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::delete('/cart/destroy', [CartController::class, 'destroies'])->name('cart.destroy.selected');

    Route::post('/cart/update/{cart:id}', [CartController::class, 'update'])->name('cart.update');

    // Route::post('/cart/checkout/store', [CartController::class, 'checkout' ])->name('cart.selected');

    Route::put('/cart/product/discount/update', [CartController::class , 'updateProductsDiscount' ]);

    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

    Route::post('/cart/checkout/', [CheckOutController::class, 'index' ])->name('checkout');

    Route::post('/cart/checkout/remove/{cart}', [CheckOutController::class, 'remove'])->name('checkout.remove');

    Route::post('/cart/checkout/placeOrder', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');

    Route::post('/checkout/placeOrder/{product}/{qty}',[PlaceOrderController::class, 'direct'])->name('checkout.placeOrder.direct');

    Route::get('/api/carts', [CartController::class, 'carts']);
   
  

  



 
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
    Route::post('/admin/products/search', [ProductController::class, 'search'])->name('admin.products.search');
    Route::put('/admin/products/{product}/status',[ProductController::class, 'changeStatus'])->name('admin.products.status.update');
    Route::post('/admin/products/status/{status}',[ProductController::class, 'changeSelectedItemStatus'])->name('admin.products.status.updates');    
   
    //image route
    Route::post('/admin/image/uploads', [ImageController::class, 'uploads']);
    Route::delete('/admin/image/{photo:id}/delete', [ImageController::class, 'unlink']);
    // orders Route
    Route::get('/admin/orders',[AdminOrderController::Class, 'index'])->name('admin.orders');
    Route::get('/admin/orders/{order}/show',[AdminOrderController::Class, 'show'])->name('admin.orders.show');
    Route::put('/admin/orders/{order}/ship',[AdminOrderController::Class, 'toShip'])->name('admin.orders.shipped');
    Route::put('/admin/orders/deliver',[AdminOrderController::Class, 'deliver'])->name('admin.orders.deliver');
    Route::get('/admin/orders/search',[AdminOrderController::Class, 'search'])->name('admin.orders.search');
    Route::get('/admin/orders/{status}',[AdminOrderController::Class, 'listbyStatus'])->name('admin.orders.list');
    
    // Route inventory
    Route::get('/admin/products/inventory', [StockController::class, 'inventory'])->name('admin.inventory');
    // Route
    Route::get('/admin/settings',[SettingController::class, 'index'])->name('admin.settings');
     

        
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
});

Route::group(['middleware' => 'auth:web'], function(){ 
     /*
    |--------------------------------------------------------------------------
    | Wishlist Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/wishlists', [WishListController::class , 'index'])->name('wishlists');

    Route::post('/wishlist/{product}', [WishListController::class , 'store'])->name('wishlist.store');

    Route::delete('/wishlist/destroy/{wishlist}', [WishListController::class , 'destroy'])->name('wishlist.destroy');

    Route::delete('/wishlist/destroy', [WishListController::class , 'destroyAll'])->name('wishlist.destroy.all');

    Route::get('/variant/{id}', [VariantController::class, 'variant'])->name('variant');

    Route::put('wishlist/update/{wishlist:id}', [WishListController::class, 'update'])->name('wishlist.product.update');

    Route::get('wishlist/addtoCart/{wishlist:id}', [WishListController::class, 'addtoCart'])->name('wishlist.toCart');

    Route::get('/wishlists/count', [WishListController::class , 'count'])->name('wishlists.count');
    Route::get('/api/wishlists', [WishListController::class , 'list']);
    Route::delete('/api/wishlists/delete/{wishlist:id}', [WishListController::class , 'delete']);



});

Route::group(['middleware' => 'auth:web'], function(){ 
    /*
   |--------------------------------------------------------------------------
   | Wishlist Routes
   |--------------------------------------------------------------------------
   */

   Route::get('/coupon/{coupon}', [CouponFrontController::class , 'index'])->name('coupon');

   Route::put('/coupon/user/{id}/remove', [CouponFrontController::class, 'remove'])->name('coupon.remove');



   Route::get('/user/active/coupon', [CouponFrontController::class , 'active' ])->name('coupon.active');


   


  


});












