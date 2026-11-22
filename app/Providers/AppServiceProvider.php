<?php
namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Listeners\SecurityEventSubscriber;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bootstrap 5 Pagination
        Paginator::useBootstrapFive();

        // Security Event Subscriber
        Event::subscribe(SecurityEventSubscriber::class);
    }
}
