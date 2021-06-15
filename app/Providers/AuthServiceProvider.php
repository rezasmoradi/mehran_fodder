<?php

namespace App\Providers;

use App\Event;
use App\Order;
use App\Payment;
use App\Policies\EventPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\TransportationPolicy;
use App\Policies\UserPolicy;
use App\Transportation;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Order::class => OrderPolicy::class,
        Transportation::class => TransportationPolicy::class,
        Payment::class => PaymentPolicy::class,
        Event::class => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addMinutes(config('auth.token_expiration.token', 1440)));
        Passport::refreshTokensExpireIn(now()->addMinutes(config('auth.token_expiration.refresh_token', 43200)));

        $this->registerGates();
    }

    private function registerGates()
    {
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        Gate::define('create-transportation', function ($user) {
            return $user->isAdmin() || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER);
        });
    }
}
