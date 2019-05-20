<?php

namespace App\MultipleMailProviders\Transport;

use GuzzleHttp\Client;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Exception;

use Illuminate\Support\Facades\Log;
use Swift_Encoding;
use Swift_Mime_SimpleMessage;
use Swift_Mime_SimpleMimeEntity;
use Swift_Transport;

class MailJetTransport extends Transport implements Swift_Transport
{

    const API_URL = 'https://api.mailjet.com/v3/send';

    /**
     * @var Client
     */
    protected $client;

    /**
     * The Mailjet public API key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * The Mailjet private API key.
     *
     * @var string
     */
    protected $privateKey;

    protected $apiUrl;

    /**
     * MailJetTransport constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = new Client(Arr::get($config, 'guzzle', []));
        $this->apiUrl = self::API_URL;
        $this->publicKey = Arr::get($config, 'public_key', null);
        $this->privateKey = Arr::get($config, 'private_key', null);
    }


    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $from = $this->getFrom($message);
        $recipients = $this->getRecipients($message);

        $message->setBcc([]);

        $options = [
            'auth' => [$this->publicKey, $this->privateKey],
            'headers' => [
                'Headers' => ['Reply-To' => $this->getReplyTo($message)],
            ],
            'json' => [
                'FromEmail' => $from['email'],
                'FromName' => $from['name'],
                'Subject' => $message->getSubject(),
                'Text-part' => $message->toString(),
                'Html-part' => $message->getBody(),
                'Recipients' => $recipients,
            ],
        ];

        /**
         * @var Swift_Mime_SimpleMimeEntity[] $attachments
         */
        if ($attachments = $message->getChildren()) {
            $options['json']['Attachments'] = array_map(
                function ($attachment) {
                    return [
                        'Content-type' => $attachment->getContentType(),
                        //'Filename' => $attachment->getFileName(),
                        'content' => Swift_Encoding::getBase64Encoding()->encodeString($attachment->getBody()),
                    ];
                }, $attachments
            );
        }
        try {

            $response = $this->client->post($this->apiUrl, $options);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Email not sent');
        }

        return $response;
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getRecipients(Swift_Mime_SimpleMessage $message)
    {
        $to = [];

        if ($message->getTo()) {
            $to = array_merge($to, $message->getTo());
        }

        if ($message->getCc()) {
            $to = array_merge($to, $message->getCc());
        }

        if ($message->getBcc()) {
            $to = array_merge($to, $message->getBcc());
        }

        $recipients = [];
        foreach ($to as $address => $name) {
            $recipients[] = ['Email' => $address, 'Name' => $name];
        }

        return $recipients;
    }

    /**
     * Get the "from" contacts in the format required by Mailjet.
     *
     * @param Swift_Mime_Message $message
     *
     * @return array
     */
    protected function getFrom(Swift_Mime_SimpleMessage $message)
    {
        return array_map(
            function ($email, $name) {
                return compact('name', 'email');
            }, array_keys($message->getFrom()), $message->getFrom()
        )[0];
    }

    /**
     * Get the 'reply_to' headers and format as required by Mailjet.
     *
     * @param Swift_Mime_Message $message
     *
     * @return string
     */
    protected function getReplyTo(Swift_Mime_SimpleMessage $message)
    {
        if (is_array($message->getReplyTo())) {
            return current($message->getReplyTo()) . ' <' . key($message->getReplyTo()) . '>';
        }
    }

    /**
     * Get the public API key being used by the transport.
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set the public API key being used by the transport.
     *
     * @param string $publicKey
     *
     * @return string
     */
    public function setPublicKey($publicKey)
    {
        return $this->publicKey = $publicKey;
    }

    /**
     * Get the private API key being used by the transport.
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Set the private API key being used by the transport.
     *
     * @param string $privateKey
     *
     * @return string
     */
    public function setPrivateKey($privateKey)
    {
        return $this->publicKey = $privateKey;
    }
}

