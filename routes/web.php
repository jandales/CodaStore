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
use App\Http\Controllers\PaymentController;
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

Route::get('/login',[UserLoginController::class, 'index'])->name('login');
Route::post('/login',[UserLoginController::class, 'login'])->name('login.store');
Route::post('/register',[RegisterController::class, 'store'])->name('register.store');
Route::get('/register', [RegisterController::class, 'index'])->name('register');


Route::controller(AppController::class)->group(function () {
    Route::get('/', 'index')->name('home'); 
    Route::get('/search', 'search')->name('search');
    Route::get('/create-cart-cookie', 'setCartCookie');
    Route::get('/about',  'about')->name('about');
    Route::get('/contact',  'contact')->name('contact');
});

Route::post('/send-message', [SendUsEmailController::class, 'send'])->name('sendMessage');

Route::controller(AppController::class)->group(function () {
    Route::get('/paypal', 'index')->name('paypal');
    Route::get('/paypal-process', 'process')->name('paypal-process');
    route::get('/paypal-success','success')->name('processSuccess');
    Route::get('/paypal-cancel','cancel')->name('processCancel');
});

/*
|--------------------------------------------------------------------------
| Cart Controller  Routes
|--------------------------------------------------------------------------
*/

Route::controller(CartController::class)->prefix('cart')->group(function () {
    Route::get('/','index')->name('cart');
    Route::post('/store/{product:id}', 'store')->name('cart.store');
    Route::delete('/destroy/{item}',  'destroy')->name('cart.destroy');
    Route::delete('/destroy',  'destroies')->name('cart.destroy.selected');
    Route::put('/update/{cartitem:id}', 'update')->name('cart.update');
    Route::put('/product/discount/update','updateProductsDiscount' );
    Route::get('/count', 'count')->name('cart.count');    
    Route::get('/coupon/activate',  'couponActivate')->name('coupon');
    Route::put('/coupon/remove',  'couponRemove')->name('coupon.remove');
    Route::get('/select/{ShippingMethod:id}/shipping-method/','selectShippingMethod')->name('cart.select.shippingMethod');    //cart ajax request 
    Route::get('/get-user-cart', 'get_user_cart');
});

// Shop route
Route::controller(ShopController::class)->prefix('shop')->group(function () {
    Route::get( '/', [ ShopController::class, 'index' ])->name( 'shop' ); 
    Route::get( '/{category:slug}', [ ShopController::class, 'category' ])->name( 'shop.category' );
    Route::get( '/item/{product:id}', [ ShopController::class, 'view' ])->name( 'shop.product' );
    Route::get('/sort-by/{value}', [ShopController::class, 'sortBy'])->name('shop.sort');
    Route::post( '/search', [ ShopController::class, 'search' ])->name( 'shop.search' );    
});

Route::get( '/product/hasvariant/{product:id}', [ ShopController::class, 'hasVariants' ])->name( 'product.hasvariants' ); 

Route::group(['middleware' => 'auth'], function(){
    Route::controller(ReviewController::class)->prefix('review')->group(function () {
        Route::post('/store/{product:id}','store')->name('review.store');
        Route::put('/{review:id}/edit/{rate}', 'update')->name('review.update');    
        Route::delete('/{review:id}','destroy')->name('review.destroy');
    });

    Route::prefix('account')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/', 'account')->name('account');   
            Route::prefix('profile')->group(function () {
                Route::get('/password','password')->name('account.password');        
                Route::get('/upload/avatar','upload')->name('account.upload');        
                Route::get('/edit', 'edit')->name('account.edit');            
                Route::put('/change-password','changePassword')->name('account.changePassword');
            });              
        }); 

        Route::controller(UserShippingAddressController::class)->prefix('shipping-address')->group(function () {                
            Route::get('/', 'index')->name('account.shippingaddress');
            Route::get('/create',  'create')->name('account.shippingaddress.create');            
            Route::post('/store', 'store')->name('account.shippingaddress.store');        
            Route::get('/{address:id}/edit',  'edit')->name('account.shippingaddress.edit');        
            Route::put('/{address:id}/update',  'update')->name('account.shippingaddress.update');        
            Route::delete('/{address:id}/destroy', 'destroy')->name('account.shippingaddress.destroy');        
            Route::put('/{address:id}/update-status',  'set_default_address')->name('account.shippingaddress.update-status');
        });
    });

    Route::controller(UserPaymentOptionController::class)->prefix('payment-option')->group(function () {
         // payment Method
        Route::get('/', 'index')->name('account.payment-option');
        Route::get('/create', 'create')->name('account.payment-option.create');    
        Route::post('/store',  'store')->name('account.payment-option.store');    
        Route::get('/edit/{option:id}',  'edit')->name('account.payment-option.edit');    
        Route::put('/update/{option:id}',  'update')->name('account.payment-option.update');    
        Route::delete('/destroy/{option:id}',  'destroy')->name('account.payment-option.destroy');    
        Route::put('/status/{option:id}',  'updateStatus')->name('account.payment-option.update.status');  

    });

    Route::controller(CheckOutController::class)->prefix('checkout')->group(function () {
        Route::get('/information', 'index')->name('checkout.information');
        Route::post('/information/store', 'store')->name('checkout.information.store');      
        Route::get('/shipping', 'shipping')->name('checkout.shipping');    
        Route::put('/shipping/update', 'updateShippingMethod')->name('checkout.shipping.update');    
        Route::get('/payment', 'payment')->name('checkout.payment');        
        Route::get('/get-shipping-charge/{id}', 'shippingCharge');  
        Route::get('/completed', 'completed')->name('checkout.completed');          
        Route::get('/order-details/{order}', 'details')->name('checkout.details');  
    });

    Route::post('/pay-now/store',  [PlaceOrderController::class, 'store'])->name('checkout.paynow');  

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/{status}', 'index')->name('account.orders');
        Route::get('/detail/{order:id}',  'details')->name('orders.details');    
        Route::get('/{product}/review',  'review')->name('orders.review');
    }); 

    Route::get('/get-shipping-method/{ShippingMethod:id}', [ShippingMethodController::class, 'getShippingMethod']);
    Route::get('/logout',[LogoutController::class, 'store'])->name('logout');
    Route::get('/variant/{id}', [VariantController::class, 'variant'])->name('variant');

});

Route::controller(OrderController::class)->group(function () {
    Route::post('/order/store', 'store')->name('orders.store');
    Route::put('/order/cancel/{order:id}','cancel')->name('orders.cancel');
});
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



Route::group(['middleware' => 'guest:admin', 'prefix' => 'admin',], function(){   

    Route::controller(DashboardController::class)->group(function (){
        Route::get('','index')->name('admin');
    });

    Route::controller(AdminLoginController::class)->group(function (){
        Route::get('/login', 'index')->name('admin.login');
        Route::post('/login', 'store')->name('admin.login.store');
    });

    Route::controller(AdminResetPasswordController::class)->group(function (){
        Route::get('/forget-password','index')->name('admin.forget.password');
        Route::post('/forget-password-request','request')->name('admin.request.password');    
        Route::get('/reset_password/{token}','reset');    
        Route::post('/reset_store','store')->name('admin.accountreset.password');
    });
  

   

    

  

   
});

Route::group(['middleware' => 'auth:admin', 'prefix' => 'admin'], function() {  

    Route::post('/logout',[LogoutController::class, 'logout'])->name('admin.logout');

    Route::controller(AdminController::class)->group(function (){       
        Route::get('/profile', 'profile')->name('admin.account');
        Route::put('/update/profile/{admin:id}',  'updateProfile')->name('admin.update.profile');
        Route::post('/update/password/{id}', 'updatePassword')->name('update.password');
        Route::post('/update/avatar/{id}',  'updateAvatar')->name('update.avatar');
    });

    Route::prefix('products')->group(function (){
        Route::controller(ProductController::class)->group(function (){
            Route::get('/','index')->name('admin.products');
            Route::get('/add','create')->name('admin.products.create');
            Route::post('/store','store')->name('admin.products.store');
            Route::put( '/update/{product:id}', 'update')->name('admin.products.update');
            Route::get('/edit/{product:id}','edit')->name('admin.products.edit');
            Route::delete('/destroy/{product:id}', 'destroy')->name('admin.products.destroy');
            Route::post('/selected-destroy', 'destroySelectedItem')->name('admin.products.destroys');      
            Route::get('/search','search')->name('admin.products.search');
            Route::put('/{product:id}/status', 'changeStatus')->name('admin.products.status.update');
            Route::post('/status/{status}', 'changeSelectedItemStatus')->name('admin.products.status.updates');    
            Route::get('/find','find')->name('admin.products.find'); 
            Route::get('/{filterBy}/{value}', 'filter')->name('admin.products.filter');  
            Route::get('/get','getProduct');     
        }); 

        Route::controller(StockController::class)->prefix('inventory')->group(function (){
            Route::get('/', 'inventory')->name('admin.inventory');
            Route::get('/filter/{filter}','filter')->name('admin.inventory.filter');
            Route::get('/adjust/search', 'search')->name('admin.inventory.search');
            Route::put('/update/stock/{stock:id}', 'updateQuantity')->name('admin.inventory.update.quantity');
        });

        Route::controller(CategoryController::class)->prefix('categories')->group(function (){
            Route::get('/', 'index')->name('admin.categories');       
            Route::post('/store', 'store')->name('categories.store');
            Route::get('/{category:slug}/edit', 'edit')->name('categories.edit');
            Route::put('/update/{category:slug}', 'update')->name('admin.categories.update');
            Route::delete('/delete/{category:slug}', 'destroy')->name('admin.categories.destroy');
            Route::delete('/selected/delete/', 'destroySelected')->name('admin.categories.selected.destroy');
            Route::get('/search', 'search')->name('admin.categories.seacrh');
        });

        Route::controller(AttributeController::class)->prefix('attributes')->group(function (){
            Route::get('/', 'index')->name('admin.attributes');
            Route::post('/store', 'store')->name('admin.attributes.store');
            Route::get('/edit/{attribute:slug}', 'edit')->name('admin.attributes.edit');
            Route::put('/update/{attribute:slug}',  'update')->name('admin.attributes.update');
            Route::delete('/{attribute:slug}/destroy', 'destroy')->name('admin.attributes.destroy');
            Route::delete('/selected-destroy','destroySelected')->name('admin.attributes.destroy.selected');
            Route::get('/p/search',  'search')->name('admin.attributes.search');
        });        

   
    });


    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('admin.dashboard');
    Route::controller(ImageController::class)->group(function (){
        Route::post('/image/uploads', 'uploads');
        Route::delete('/image/{photo:id}/delete', 'unlink');
    });
    
    Route::controller(AdminOrderController::Class)->prefix('orders')->group(function (){
        Route::get('/','index')->name('admin.orders');
        Route::get('/{order:id}/show', 'show')->name('admin.orders.show');
        Route::put('/ship/{order:id}', 'toShip')->name('admin.orders.shipped');
        Route::put('/update-status/{order:id}','updateStatus')->name('admin.orders.shipped1');
        Route::put('/deliver', 'deliver')->name('admin.orders.deliver');
        Route::get('/search', 'search')->name('admin.orders.search');
        Route::get('/{status}', 'listbyStatus')->name('admin.orders.list');
        Route::delete('/destroy/{order:id}', 'destroy')->name('admin.orders.destroy');
    });

    
    Route::controller(CouponController::class)->prefix('coupons')->group(function (){
        Route::get('/', 'index')->name('admin.coupons');
        Route::get('/create','create')->name('admin.coupon.create');
        Route::get('/edit/{coupon:id}','edit')->name('admin.coupon.edit');
        Route::get('/show/{coupon:id}','show')->name('admin.coupon.show');
        Route::post('/store','store')->name('admin.coupon.store');
        Route::put('/update/{coupon:id}','update')->name('admin.coupon.update');
        Route::delete('/destroy/{coupon:id}','destroy')->name('admin.coupon.destroy');
        Route::delete('/destroy-selected-item','destroySelectedItem')->name('admin.coupon.destroy.selected');
        Route::get('/search','search')->name('admin.coupons.search');
    });
 


    Route::prefix('setting')->group(function () {
        Route::controller(SettingController::class)->group(function (){
            Route::get('/general','general')->name('admin.setting.general');
            Route::get('/campany','campany')->name('admin.setting.campany');
            Route::get('/email','emailer')->name('admin.setting.emailer');    
            Route::put('/general/update/{general_setting}','updateGeneral')->name('admin.setting.general.update');
            Route::put('/general/campany/update/{general_setting}','updateCampanyAddress')->name('admin.setting.campany.update');
        });
        Route::controller(SocialSiteController::class)->prefix('social')->group(function (){
            Route::get('/','index')->name('admin.setting.social');
            Route::post('/store','store')->name('admin.setting.social.store');
            Route::put('/update/{site}','update')->name('admin.setting.social.update');
            Route::delete('/destroy/{site}','destroy')->name('admin.setting.social.destroy');
        });
        Route::controller(ShippingMethodController::class)->prefix('shipping-method')->group(function (){
            Route::get('/index', 'index')->name('admin.shipping.method');
            Route::get('/create', 'create')->name('admin.shipping.method.create');
            Route::post('/store','store')->name('admin.shipping.method.store');
            Route::get('/search', 'search')->name('admin.shipping.method.search');   
            Route::get('/{method:id}/edit','edit')->name('admin.shipping.method.edit');
            Route::put('/{method:id}/update','update')->name('admin.shipping.method.update');
            Route::delete('/{method:id}/destroy','destroy')->name('admin.shipping.method.destroy');
            Route::delete('/selected-destroy', 'selected_destroy')->name('admin.shipping.method.selected.destroy');
            Route::put('/update-status/{method}/{status}','update_status')->name('admin.shipping.method.update.status');
            Route::put('/selected-update-status','selected_update_status')->name('admin.shipping.method.selected.update.status');
        });

     
    
      
    });
    Route::controller(ReviewController::class)->prefix('review')->group(function (){
        Route::get('/', 'index')->name('admin.reviews');
        Route::get('block/{status}', 'listbyStatus')->name('admin.reviews.list');
        Route::put('/block/{review:id}', 'block')->name('admin.reviews.block');
        Route::delete('/destroy/{review:id}', 'destroy')->name('admin.reviews.destroy');
        Route::delete('/selected/destroy','destroySelected')->name('admin.reviews.selected.destroy');
        Route::get('/search', 'search')->name('admin.reviews.search');
    });

    Route::controller(AdminController::class)->prefix('users')->group(function () {
        Route::get('/','index')->name('admin.users');
        Route::get('/create',  'create')->name('admin.users.create');    
        Route::post('/store',  'store')->name('admin.users.store');    
        Route::get('/edit/{admin:id}',  'edit')->name('admin.users.edit');    
        Route::put('/update/{admin:id}', 'update')->name('admin.users.update');    
        Route::post('/destroy/{admin:id}','destroy')->name('admin.users.destroy');    
        Route::post('/selected/destory', 'destroySelectedItem')->name('admin.users.destroySelectedItem');    
        Route::put('/change-selected-item-role-to/', 'updateSelectItemRoleTo')->name('admin.users.updateSelectItemRoleTo');           
        Route::get('/search',  'search')->name('admin.users.search');    
        Route::get('/show/{admin:id}',  'show')->name('admin.users.show');    
        Route::post('/send-reset-password/{admin:id}',  'sentResetPassword')->name('admin.users.sentPasswordResetPassword');    
        Route::put('/changepassword/{admin:id}','updatePassword')->name('users.updatePassword');
    });

    Route::controller(UserController::class)->prefix('customers')->group(function () {
        Route::get('/','index')->name('admin.customers');
        Route::get('/show/{user:id}','show')->name('admin.customers.show');
        Route::delete('/destroy/{user:id}','destroy')->name('admin.customers.destroy');
        Route::delete('/selected/destroy', 'selectedDestroy')->name('admin.customers.selected.destroy');
        Route::get('/search', 'search')->name('admin.customers.search'); 
    });

    Route::controller(InboxController::class)->prefix('inboxes')->group(function () {
        Route::get('/','index')->name('admin.inbox');
        Route::get('/{id}','view')->name('admin.inbox.show');
        Route::patch('/{id}','update')->name('admin.inbox.update');
        Route::delete('/{id}', 'destroy')->name('admin.inbox.destroy');
        Route::patch('/unread/{id}','unread')->name('admin.inbox.unread');
    });

    

 
        

    Route::get('/attributes/all', [AttributeController::class, 'getAll']);
    Route::get('/api/products/categories', [CategoryController::class, 'lists'])->name('admin.categories.all');
    Route::post('/variants/store', [VariantController::class, 'store'])->name('variants.store');
    Route::delete('/variants/destroy/{variant:id}', [VariantController::class, 'destroy'])->name('variants.destroy');

    /*
|--------------------------------------------------------------------------
| End main Rotue
|--------------------------------------------------------------------------
*/
    
});

    


Route::post('/users/update',[UserController::class, 'update'])->name('users.update');

Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('password.forgot');
Route::post('/forgot-password', [ForgotPasswordController::class, 'request'])->name('password.request');
Route::get('/reset_password/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('/reset_password', [ResetPasswordController::class, 'reset'])->name('password.store');
Route::get('/send',[ForgotPasswordController::class, 'send'])->name('sent');
Route::post('/upload/avatar',[UserController::class, 'avatar'])->name('upload.avatar');
































