<?php

namespace App\Jobs;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Markdown;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Function to send email
     */
    public function sendEmail()
    {
        $sent = false;

        $mailProviders = array_keys((array)config('multipleMailProviders'));
        $content = $this->getContent();

        if (!empty($mailProviders)) {
            foreach ($mailProviders as $mailProvider) {
                for ($i = 0; $i < $this->maxAttempts; $i++) {
                    //increment attempts
                    $this->email->attempts += 1;

                    try {
                        Log::error($mailProvider);
                        Mail::mailDriver($mailProvider)->send($content, [], function($message)
                        {
                            $message->from(config('mail.from.address'), config('mail.from.name'))
                                ->to($this->email->to)
                                ->subject($this->email->subject);
                        });
                        $sent = true;
                        $this->email->service = $mailProvider;
                        $this->email->status = Email::STATUS_SENT;
                        $this->email->save();
                        break 2;
                    } catch (\Exception $exception) {
                        Log::error($exception->getMessage());
                    }
                }
            }

            if (!$sent) {
                $this->email->status = Email::STATUS_FAILED;
                $this->email->save();
                Log::error('Email not sent');
            }
        }
    }

    /**
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    private function getContent()
    {
        $content = [];
        switch ($this->email->type){
            case Email::TYPE_TEXT_PLAIN :
                $content = ['raw' => $this->email->content];
                break;
            case Email::TYPE_TEXT_MARKDOWN :
                $content = ['html' => Markdown::parse($this->email->content)];
                break;
            case Email::TYPE_TEXT_HTML :
                $content = ['html' => $this->email->content];
                break;
            default :
                \Log::error('Invalid email type: ' . $this->email->type);
        }

        return $content;
    }
}
