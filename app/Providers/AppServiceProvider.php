<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Observers\CaseObserver;
use App\Observers\DocumentObserver;
use App\Observers\PaymentTransactionObserver;
use App\Observers\UserObserver;
use App\Policies\AdminPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\CasePolicy;
use App\Policies\InvoicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // ─── Policies ────────────────────────────────────────────────
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(User::class,        AdminPolicy::class);

        Gate::policy(LegalCase::class, CasePolicy::class);
        Gate::policy(Invoice::class,   InvoicePolicy::class);

        // ─── Observers ───────────────────────────────────────────────
        LegalCase::observe(CaseObserver::class);
        Document::observe(DocumentObserver::class);
        PaymentTransaction::observe(PaymentTransactionObserver::class);
        User::observe(UserObserver::class);
    }
}