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
use App\Http\Controllers\CheckoutPaymentController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\CustomerController;
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
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPaymentController;
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

// Email verification (checkout needs a confirmed address).
Route::controller(\App\Http\Controllers\Auth\User\EmailVerificationController::class)->middleware('auth')->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', 'send')->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/auth/google/login', [UserLoginController::class, 'loginGoogle'])->name('authGoogle');
Route::get('/auth/google/callback-url', [UserLoginController::class, 'handleProviderCallback'])->name('handleProviderCallback');

Route::controller(AppController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/search', 'search')->name('search');
    Route::get('/create-cart-cookie', 'setCartCookie');
    Route::get('/about',  'about')->name('about');
    Route::get('/contact',  'contact')->name('contact');
});

Route::post('/send-message', [SendUsEmailController::class, 'send'])->name('sendMessage');

// SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots'])->name('robots');
// Newsletter and bag reminders
Route::post('/newsletter', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [\App\Http\Controllers\NewsletterController::class, 'confirmUnsubscribe'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{token}', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.confirm');
Route::get('/cart/restore/{cart}', [\App\Http\Controllers\CartReminderController::class, 'restore'])->middleware(['signed', 'auth'])->name('cart.restore');
Route::get('/cart-reminders/off/{user}', [\App\Http\Controllers\CartReminderController::class, 'off'])->middleware('signed')->name('cart.reminders.off');

Route::get('/pages/{slug}', [\App\Http\Controllers\PageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('page');


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

    // Wishlist (heart button posts here; guests are sent to sign in by the auth middleware).
    Route::controller(WishlistController::class)->group(function () {
        Route::get('/account/wishlist', 'index')->name('account.wishlist');
        Route::post('/wishlist/{product:id}', 'toggle')->name('wishlist.toggle');
        Route::delete('/wishlist/{product:id}', 'destroy')->name('wishlist.remove');
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


    Route::controller(CheckOutController::class)->prefix('checkout')->middleware('verified')->group(function () {
        Route::get('/information', 'index')->name('checkout.information');
        Route::post('/information/store', 'store')->name('checkout.information.store');
        Route::get('/shipping', 'shipping')->name('checkout.shipping');
        Route::put('/shipping/update', 'updateShippingMethod')->name('checkout.shipping.update');
        Route::get('/payment', 'payment')->name('checkout.payment');
        Route::get('/get-shipping-charge/{id}', 'shippingCharge');
        Route::get('/completed', 'completed')->name('checkout.completed');
        Route::get('/order-details/{order}', 'details')->name('checkout.details');
    });

    // Payment happens on PayPal; the order is created only after PayPal confirms the capture.
    Route::controller(CheckoutPaymentController::class)->prefix('checkout/paypal')->middleware('verified')->group(function () {
        Route::post('/', 'start')->name('checkout.paypal.start');
        Route::get('/return', 'return')->name('checkout.paypal.return');
        Route::get('/cancel', 'cancel')->name('checkout.paypal.cancel');
    });

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/{status}', 'index')->name('account.orders');
        Route::get('/detail/{order:id}',  'details')->name('orders.details');
        Route::get('/invoice/{order:id}', 'invoice')->name('orders.invoice');
        Route::post('/return/{order:id}', 'requestReturn')->name('orders.return');
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

    Route::prefix('products')->middleware('permission:products')->group(function (){
        Route::controller(\App\Http\Controllers\Admin\ProductImportController::class)->prefix('import')->group(function () {
            Route::get('/', 'create')->name('admin.products.import');
            Route::get('/template.csv', 'template')->name('admin.products.import.template');
            Route::post('/preview', 'preview')->name('admin.products.import.preview');
            Route::post('/run', 'run')->name('admin.products.import.run');
        });

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
    Route::controller(ImageController::class)->middleware('permission:products')->group(function (){
        Route::post('/image/uploads', 'uploads');
        Route::delete('/image/{photo:id}/delete', 'unlink');
    });

    Route::controller(AdminOrderController::Class)->prefix('orders')->middleware('permission:orders')->group(function (){
        Route::get('/','index')->name('admin.orders');
        Route::get('/{order:id}/show', 'show')->name('admin.orders.show');
        Route::put('/ship/{order:id}', 'toShip')->name('admin.orders.shipped');
        Route::put('/update-status/{order:id}','updateStatus')->name('admin.orders.shipped1');
        Route::put('/deliver', 'deliver')->name('admin.orders.deliver');
        Route::get('/search', 'search')->name('admin.orders.search');
        Route::get('/returns', 'returns')->name('admin.orders.returns');
        Route::get('/{order:id}/invoice', 'invoice')->name('admin.orders.invoice');
        Route::get('/{order:id}/packing-slip', 'packingSlip')->name('admin.orders.packing-slip');
        Route::put('/{order:id}/return/approve', 'approveReturn')->name('admin.orders.return.approve');
        Route::put('/{order:id}/return/reject', 'rejectReturn')->name('admin.orders.return.reject');
        Route::post('/{order:id}/notes', 'storeNote')->name('admin.orders.notes.store');
        Route::delete('/{order:id}/notes/{note}', 'destroyNote')->name('admin.orders.notes.destroy');
        Route::get('/{status}', 'listbyStatus')->name('admin.orders.list');
        Route::delete('/destroy/{order:id}', 'destroy')->name('admin.orders.destroy');
    });

    Route::controller(\App\Http\Controllers\Admin\AdminExportController::class)->prefix('exports')->group(function () {
        Route::get('/orders.csv', 'orders')->middleware('permission:orders')->name('admin.exports.orders');
        Route::get('/customers.csv', 'customers')->middleware('permission:customers')->name('admin.exports.customers');
        Route::get('/newsletter.csv', 'subscribers')->middleware('permission:customers')->name('admin.exports.subscribers');
        Route::get('/products.csv', 'products')->middleware('permission:products')->name('admin.exports.products');
    });

    Route::controller(AdminPaymentController::class)->prefix('payments')->middleware('permission:orders')->group(function () {
        Route::get('/', 'index')->name('admin.payments');
    });


    Route::controller(CouponController::class)->prefix('coupons')->middleware('permission:coupons')->group(function (){
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



    Route::controller(\App\Http\Controllers\Admin\PageController::class)->prefix('pages')->middleware('permission:settings')->group(function () {
        Route::get('/', 'index')->name('admin.pages');
        Route::get('/create', 'create')->name('admin.pages.create');
        Route::post('/', 'store')->name('admin.pages.store');
        Route::get('/{page}/edit', 'edit')->name('admin.pages.edit');
        Route::put('/{page}', 'update')->name('admin.pages.update');
        Route::delete('/{page}', 'destroy')->name('admin.pages.destroy');
    });

    Route::prefix('setting')->middleware('permission:settings')->group(function () {
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
    Route::controller(ReviewController::class)->prefix('review')->middleware('permission:customers')->group(function (){
        Route::get('/', 'index')->name('admin.reviews');
        Route::get('block/{status}', 'listbyStatus')->name('admin.reviews.list');
        Route::put('/block/{review:id}', 'block')->name('admin.reviews.block');
        Route::delete('/destroy/{review:id}', 'destroy')->name('admin.reviews.destroy');
        Route::delete('/selected/destroy','destroySelected')->name('admin.reviews.selected.destroy');
        Route::get('/search', 'search')->name('admin.reviews.search');
    });

    // Staff accounts and the activity log: administrators only.
    Route::get('/activity', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->middleware('permission:staff')->name('admin.activity');

    Route::controller(AdminController::class)->prefix('users')->middleware('permission:staff')->group(function () {
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

    Route::controller(UserController::class)->prefix('customers')->middleware('permission:customers')->group(function () {
        Route::get('/','index')->name('admin.customers');
        Route::get('/show/{user:id}','show')->name('admin.customers.show');
        Route::delete('/destroy/{user:id}','destroy')->name('admin.customers.destroy');
        Route::delete('/selected/destroy', 'selectedDestroy')->name('admin.customers.selected.destroy');
        Route::get('/search', 'search')->name('admin.customers.search');
    });

    Route::controller(InboxController::class)->prefix('inboxes')->middleware('permission:customers')->group(function () {
        Route::get('/','index')->name('admin.inbox');
        Route::get('/{id}','view')->name('admin.inbox.show');
        Route::patch('/{id}','update')->name('admin.inbox.update');
        Route::delete('/{id}', 'destroy')->name('admin.inbox.destroy');
        Route::patch('/unread/{id}','unread')->name('admin.inbox.unread');
    });






    Route::get('/attributes/all', [AttributeController::class, 'getAll'])->middleware('permission:products');
    Route::get('/api/products/categories', [CategoryController::class, 'lists'])->middleware('permission:products')->name('admin.categories.all');
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

































