<?php

namespace App\Jobs;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use \Mailjet\Resources;

class ProcessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Email */
    private $email;

    /** @var array  */
    private $mailProviders = [];

    /** @var int  */
    private $maxAttempts = 3;

    /**
     * Create a new job instance.
     *
     * @param Email $email
     *
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
        $this->mailProviders = Email::mailProviders();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('New email job: ' . $this->email);

        if (empty($this->mailProviders)) {
            Log::info('No email providers set');
        }

        $this->sendEmail();
    }

    /**
     * @throws \SendGrid\Mail\TypeException
     */
    public function sendEmail()
    {
        $sent = false;
        foreach ($this->mailProviders as $mailProvider) {
            if (!$sent) {
                switch ($mailProvider) {
                    case Email::MAIL_PROVIDER_MAILJET :
                        $sent = $this->useMailJet();
                        break;
                    case Email::MAIL_PROVIDER_SENDGRID :
                        $sent = $this->useSendGrid();
                        break;
                    default :
                        \Log::error('Invalid email provider');
                }
            } else {
                break;
            }
        }

        if (!$sent) {
            $this->email->status = Email::STATUS_FAILED;
            $this->email->save();
            \Log::error('Email not sent');
        }
    }

    /**
     * @return bool
     */
    public function useMailJet()
    {
        \Log::info("Start send email using MailJet");

        $mj = new \Mailjet\Client(env('MAILJET_API_KEY'), env('MAILJET_API_SECRET'),true,['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('EMAIL_FROM'),
                        'Name' => env('EMAIL_FROM'),
                    ],
                    'To' => [
                        [
                            'Email' => $this->email->to,
                            'Name' => $this->email->to,
                        ]
                    ],
                    'Subject' => $this->email->subject,
                ]
            ]
        ];

        switch ($this->email->type){
            case Email::TYPE_TEXT_PLAIN :
                $body['Messages'][0]['TextPart'] = $this->email->content;
                break;
            case Email::TYPE_TEXT_MARKDOWN :
                $body['Messages'][0]['TextPart'] = $this->email->content;
                break;
            case Email::TYPE_TEXT_HTML :
                $body['Messages'][0]['HTMLPart'] = $this->email->content;
                break;
            default :
                \Log::error('Invalid email type: ' . $this->email->type);

        }

        for ($i = 0; $i < $this->maxAttempts; $i++) {
            $response = $mj->post(Resources::$Email, ['body' => $body]);
            //increment attempts
            $this->email->attempts += 1;
            if ($response->success()) {
                \Log::info($response->getData());
                //set email provider sent
                $this->email->service = Email::MAIL_PROVIDER_MAILJET;
                $this->email->status = Email::STATUS_SENT;
                $this->email->save();
                return true;
            }
            $this->email->save();
        }
        return false;
    }

    /**
     * @return bool
     * @throws \SendGrid\Mail\TypeException
     */
    public function useSendGrid()
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(env('EMAIL_FROM'), env('EMAIL_FROM'));
        $email->setSubject($this->email->subject);
        $email->addTo($this->email->to, $this->email->to);
        $email->addContent($this->email->type, $this->email->content);
        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

        for ($i = 0; $i < $this->maxAttempts; $i++) {
            //increment attempts
            $this->email->attempts += 1;
            try {
                $response = $sendgrid->send($email);
                \Log::info(json_encode($response->headers()));
                $this->email->service = Email::MAIL_PROVIDER_SENDGRID;
                $this->email->status = Email::STATUS_SENT;
                $this->email->save();
                return true;
            } catch (\Exception $exception) {
                \Log::info($exception->getMessage());
                $this->email->save();
            }
        }
        return false;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
