<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Product::class => App\Policies\ProductPolicy::class,
        Admin::class => App\Policies\AdminPolicy::class,
        Attribute::class => App\Policies\AttributePolicy::class,
        Category::class => App\Policies\Category::class,
        User::class => App\Policies\UserPolicy::class,
        Order::class => App\Policies\OrderPolicy::class,
        UserPaymentOption::class => App\Policies\UserPaymentOptionPolicy::class,
        UserShippingAddress::class => App\Policies\UserShippingAddressPolicy::class,
        Inbox::class => App\Policies\InboxPolicy::class,
        Coupon::class => App\Polocies\CouponPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
    }
}
