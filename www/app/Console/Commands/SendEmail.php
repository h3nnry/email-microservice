<?php

namespace App\Console\Commands;

use App\Email;
use Illuminate\Console\Command;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send '
    . '{to : email recipient, for multiple separate by ","} '
    . '{content :  email content} '
    . '{subject : email subject} '
    . '{type : email type, } available options: "text/plain", "text/markdown", "text/html"';

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
    public function __construct()
    {
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
            $results = Email::createNew($this->arguments());
            if (!empty($results['errors'])) {
                $this->info(json_encode($results['errors']));
            } else {
                $this->info('Emails with ids: ' . implode(', ', $results['results']) . ' expecting to be sent.');
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
