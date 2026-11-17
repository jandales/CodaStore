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

        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Confirm your email for Coda')
                ->greeting('Hi ' . ($notifiable->name ?: 'there') . ',')
                ->line('Please confirm your email address so we can send your order updates.')
                ->action('Confirm email', $url)
                ->line('The link works for 60 minutes. If you didn\'t create a Coda account, you can ignore this email.');
        });

    }
}
