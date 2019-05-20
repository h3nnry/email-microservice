<?php

namespace App\Providers;

use App\MultipleMailProviders\Mailer;
use Illuminate\Support\ServiceProvider;

class MultipleMailersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Mailer::class, function ($app)
        {
            return new Mailer(
                $app['view'], $app['swift.mailer'], $app['events']
            );
        });
    }
}
