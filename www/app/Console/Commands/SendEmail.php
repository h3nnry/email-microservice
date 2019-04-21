<?php

namespace App\Console\Commands;

use App\Repositories\EmailRepository;
use App\Email;
use App\Jobs\ProcessEmail;
use App\Http\Requests\EmailRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * Class SendEmail
 * @package App\Console\Commands
 */
class SendEmail extends Command
{
    private $emailRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send '
    . '{to : email recipient, for multiple separate by ","} '
    . '{content :  email content} '
    . '{subject : email subject} '
    . '{type : email type, available options: "text/plain", "text/markdown", "text/html"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email from console';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $arguments = $this->arguments();
            $validator = Validator::make($arguments, (new EmailRequest)->rules());
            if ($validator->fails()) {
                $this->info(json_encode($validator->errors()));
            } else {
                $saveData = Arr::only($arguments, ['subject', 'content', 'type']);
                $saveData['status'] = Email::STATUS_QUEUED;
                $receivers = explode(',', $arguments['to']);
                $result = [];
                foreach ($receivers as $to) {
                    $saveData['to'] = $to;
                    $email = $this->emailRepository->create($saveData);
                    $result[] = $email->id;
                    dispatch(new ProcessEmail($email));
                }
                $this->info('Emails with ids: ' . implode(', ', $result) . ' expecting to be sent.');
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
