<?php
namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
   
    public function register(): void
    {
        
    }


    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
    
    public function boot(): void
    {
        Event::subscribe(\App\Listeners\SecurityEventSubscriber::class);
    }

}
