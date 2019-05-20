<?php

namespace App\MultipleMailProviders;

use Illuminate\Support\Manager;
use Swift_Mailer;

class SwiftMailerManager extends Manager
{
    /** @var TransportManager */
    protected $transportManager;

    /**
     * @return TransportManager
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * @param TransportManager $manager
     * @return $this
     */
    public function setTransportManager(TransportManager $manager)
    {
        $this->transportManager = $manager;

        return $this;
    }

    /**
     * @param null $driver
     * @return mixed
     */
    public function mailer($driver = null)
    {
        return $this->driver($driver);
    }

    /**
     * @return array
     */
    public function getMailers()
    {
        return $this->drivers;
    }

    /**
     * @param Swift_Mailer $mailer
     * @return null
     */
    public function getDriverForMailer(Swift_Mailer $mailer)
    {
        return array_search($mailer, $this->drivers) ?: null;
    }

    /**
     * @param $mailer
     * @return $this
     */
    public function resetMailer($mailer)
    {
        if ($driver = $this->validDriverName($mailer)) {
            unset($this->drivers[$driver]);
            $this->transportManager->resetDriver($driver);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetMailers()
    {
        $this->drivers = [];
        $this->transportManager->resetDrivers();

        return $this;
    }

    /**
     * @param $driver
     * @return null
     */
    protected function validDriverName($driver)
    {
        if ($driver instanceof Swift_Mailer) {
            $driver = $this->getDriverForMailer($driver);
        }

        if (is_string($driver)) {
            return $driver;
        }
    }

    /**
     * @param string $driver
     * @return mixed|Swift_Mailer
     */
    protected function createDriver($driver)
    {
        return new Swift_Mailer($this->transportManager->driver($driver));
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->transportManager->getDefaultDriver();
    }

    /**
     * @param $driver
     * @return $this
     */
    public function setDefaultDriver($driver)
    {
        $this->transportManager->setDefaultDriver($driver);

        return $this;
    }

    /**
     * @param $mailer
     * @return $this
     */
    public function setDefaultMailer($mailer)
    {
        if ($driver = $this->validDriverName($mailer)) {
            $this->setDefaultDriver($driver);
        }

        return $this;
    }
}