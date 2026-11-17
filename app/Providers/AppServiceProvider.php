<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Contracts\PaymentGateway::class, \App\Services\Payments\PayPalGateway::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(config('app.env') != 'local') {
            \URL::forceScheme('https');
        }

        if(Schema::hasTable('categories')) {
            View::share('categories', Category::All());
        }       
        if(Schema::hasTable('attributes')){
            View::share('attributes', Attribute::All());
        } 

        Blade::directive('money', function ($amount) {
            return "<?php echo '₱'.number_format($amount, 2); ?>";
        });

         Paginator::defaultView('vendor.pagination.default');

        // Activity log: changes made by staff in the admin.
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\Stock::observe(\App\Observers\StockObserver::class);

    }
}
