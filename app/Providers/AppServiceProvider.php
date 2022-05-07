<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('categories', Category::All());
        View::share('attributes', Attribute::All());
     
        
        Blade::directive('money', function ($amount) {
            return "<?php echo '₱'.number_format($amount, 2); ?>";
        });

        Paginator::defaultView('vendor.pagination.default');

    }
}
