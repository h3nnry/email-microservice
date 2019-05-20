<?php

namespace App\MultipleMailProviders;

use Illuminate\Mail\TransportManager as BaseTransportManager;

class TransportManager extends BaseTransportManager
{
    /**
     * @param string $name
     */
    public function resetDriver($name)
    {
        unset($this->drivers[$name]);
    }

    /**
     *
     */
    public function resetDrivers()
    {
        $this->drivers = [];
    }
}