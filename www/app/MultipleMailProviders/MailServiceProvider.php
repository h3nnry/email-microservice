<?php

namespace App\MultipleMailProviders;

use Illuminate\Mail\MailServiceProvider as BaseServiceProvider;

class MailServiceProvider extends BaseServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->register(MailTransportServiceProvider::class);

        $this->registerSwiftMailer();

        $this->registerMailer();
    }

    /**
     *
     */
    protected function registerMailer()
    {
        $this->app->singleton('mailer', function ($app) {
            $mailer = new Mailer(
                $app['view'], $app['swift.mailer'], $app['events']
            );

            $this->setMailerDependencies($mailer, $app);

            $from = $app['config']['mail.from'];

            if (is_array($from) && isset($from['address'])) {
                $mailer->alwaysFrom($from['address'], $from['name']);
            }

            $to = $app['config']['mail.to'];

            if (is_array($to) && isset($to['address'])) {
                $mailer->alwaysTo($to['address'], $to['name']);
            }

            return $mailer;
        });

        $this->app->alias('mailer', Mailer::class);
    }

    /**
     * @param $mailer
     * @param $app
     */
    protected function setMailerDependencies($mailer, $app)
    {
        $mailer->setSwiftMailerManager($app['swift.manager']);
    }

    /**
     *
     */
    public function registerSwiftMailer()
    {
        $this->registerSwiftTransport();

        $this->registerSwiftMailerManager();

        $this->app->bind('swift.mailer', function ($app) {
            return $app['swift.manager']->mailer();
        });
    }

    /**
     *
     */
    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function ($app) {
            return new TransportManager($app);
        });

        $this->app->alias('swift.transport', TransportManager::class);
    }

    /**
     *
     */
    protected function registerSwiftMailerManager()
    {
        $this->app->singleton('swift.manager', function ($app) {
            return (new SwiftMailerManager($app))
                ->setTransportManager($app['swift.transport']);
        });

        $this->app->alias('swift.manager', SwiftMailerManager::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return array_merge(parent::provides(), [
            'swift.manager',
            Mailer::class,
            SwiftMailerManager::class,
            TransportManager::class,
        ]);
    }
}