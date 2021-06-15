<?php

namespace App\Providers;

use App\Events\UpdateProductsEvent;
use App\Listeners\ActiveUnregisteredUserAfterLogin;
use App\Listeners\UpdateProductsTableListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UpdateProductsEvent::class => [
            UpdateProductsTableListener::class,
        ],

        AccessTokenCreated::class => [
            ActiveUnregisteredUserAfterLogin::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
