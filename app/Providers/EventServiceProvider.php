<?php

namespace App\Providers;

use App\Events\AppointmentConfirmed;
use App\Events\PaymentConfirmed;
use App\Listeners\HandleAppointmentConfirmed;
use App\Listeners\HandlePaymentConfirmed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AppointmentConfirmed::class => [
            HandleAppointmentConfirmed::class,
        ],
        PaymentConfirmed::class => [
            HandlePaymentConfirmed::class,
        ],
    ];

    /**
     * Enable the application to listen for events
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
