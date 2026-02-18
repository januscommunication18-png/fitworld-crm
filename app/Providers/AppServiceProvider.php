<?php

namespace App\Providers;

use App\Models\ClassRequest;
use App\Models\HelpdeskTicket;
use App\Models\WaitlistEntry;
use App\Observers\ClassRequestObserver;
use App\Observers\HelpdeskTicketObserver;
use App\Observers\WaitlistEntryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        HelpdeskTicket::observe(HelpdeskTicketObserver::class);
        ClassRequest::observe(ClassRequestObserver::class);
        WaitlistEntry::observe(WaitlistEntryObserver::class);
    }
}
