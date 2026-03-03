<?php

namespace App\Providers;

use App\Events\ClassSessionPublished;
use App\Events\MembershipActivated;
use App\Listeners\EnrollMemberIntoScheduledClasses;
use App\Listeners\EnrollScheduledMembersIntoSession;
use App\Listeners\TrackUserLogin;
use App\Listeners\TrackUserLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\ClassRequest;
use App\Models\Client;
use App\Models\HelpdeskTicket;
use App\Models\WaitlistEntry;
use App\Observers\ClassRequestObserver;
use App\Observers\ClientObserver;
use App\Observers\HelpdeskTicketObserver;
use App\Observers\WaitlistEntryObserver;
use App\View\Composers\TranslationViewComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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
        Client::observe(ClientObserver::class);

        // Register event listeners for scheduled membership auto-enrollment
        Event::listen(ClassSessionPublished::class, EnrollScheduledMembersIntoSession::class);
        Event::listen(MembershipActivated::class, EnrollMemberIntoScheduledClasses::class);

        // Register event listeners for session tracking
        Event::listen(Login::class, TrackUserLogin::class);
        Event::listen(Logout::class, TrackUserLogout::class);

        // Register translation view composer for all views that need translations
        View::composer([
            'host.*',
            'layouts.dashboard',
            'subdomain.*',
            'layouts.subdomain',
        ], TranslationViewComposer::class);
    }
}
