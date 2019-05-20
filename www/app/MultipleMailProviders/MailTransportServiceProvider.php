<?php

namespace App\MultipleMailProviders;

use Illuminate\Mail\TransportManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class MailTransportServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function(TransportManager $manager) {
            $this->extendTransportManager($manager);
        });
    }

    /**
     * @param TransportManager $manager
     * @throws \ReflectionException
     */
    public function extendTransportManager(TransportManager $manager)
    {
        $multipleMailProviders = $this->app['config']->get('multipleMailProviders', []);
        if (!empty($multipleMailProviders)) {
            try {
                foreach ($multipleMailProviders as $multipleMailProviderKey => $multipleMailProviderValue) {
                    $className = 'App\MultipleMailProviders\Transport\\' . ucfirst($multipleMailProviderKey) . 'Transport';
                    if (is_subclass_of($className, 'Illuminate\Mail\Transport\Transport')) {
                        $rc = new ReflectionClass($className);
                        $transport = $rc->newInstanceArgs([$multipleMailProviderValue]);
                        $manager
                            ->extend($multipleMailProviderKey, function() use ($transport) {
                                return $transport;
                            });
                    }
                }
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
            }

        }
    }
}