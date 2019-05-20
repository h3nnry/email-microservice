<?php

namespace App\MultipleMailProviders;

use Closure;
use Illuminate\Mail\Mailer as BaseMailer;
use Swift_Mailer;

class Mailer extends BaseMailer
{
    /** @var SwiftMailerManager */
    protected $swiftManager;

    /** @var \Closure|string */
    protected $sendingMessageHandler;

    /**
     * @return SwiftMailerManager
     */
    public function getSwiftMailerManager()
    {
        return $this->swiftManager;
    }

    /**
     * @param SwiftMailerManager $manager
     * @return $this
     */
    public function setSwiftMailerManager(SwiftMailerManager $manager)
    {
        $this->swiftManager = $manager;

        return $this;
    }

    /**
     * @param $handler
     * @return $this
     */
    public function registerSendingMessageHandler($handler)
    {
        $this->sendingMessageHandler = $handler;

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return mixed
     */
    protected function callSendingMessageHandler(...$args)
    {
        if ($this->sendingMessageHandler instanceof Closure) {
            return $this->container->call($this->sendingMessageHandler, $args);
        }

        if (is_string($this->sendingMessageHandler)) {
            return $this->container->call($this->sendingMessageHandler, $args, 'sendingMail');
        }
    }

    /**
     * @param $message
     * @return mixed|Swift_Mailer
     */
    protected function getSwiftMailerForMessage($message)
    {
        $driver = $this->callSendingMessageHandler($message, $this);

        if ($driver instanceof Swift_Mailer) {
            return $driver;
        }

        return $this->getSwiftMailer($driver);
    }

    /**
     * @param \Swift_Message $message
     * @return int|null
     */
    protected function sendSwiftMessage($message)
    {
        $swift = $this->getSwiftMailerForMessage($message);

        try {
            return $swift->send($message, $this->failedRecipients);
        } finally {
            $swift->getTransport()->stop();
        }
    }

    /**
     * @param null $driver
     * @return Swift_Mailer
     */
    public function getSwiftMailer($driver = null)
    {
        return $this->swiftManager->mailer($driver);
    }

    /**
     * @param Swift_Mailer $swift
     * @return $this|void
     */
    public function setSwiftMailer($swift)
    {
        $this->swiftManager->setDefaultMailer($swift);

        return $this;
    }

    /**
     * @param $driver
     * @return $this
     */
    public function mailDriver($driver)
    {
        $this->swiftManager->setDefaultDriver($driver);

        return $this;
    }
}