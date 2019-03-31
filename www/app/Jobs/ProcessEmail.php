<?php

namespace App\Jobs;

use App\Email;

class ProcessEmail extends Job
{

    /** @var Email */
    protected $email;

    /**
     * ProcessUserMail constructor.
     * @param Email $email
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        \Log::info('New email job: ' . $this->email);

        //Send email and increment attempts
        $this->email->attempts += 1;
        $this->email->save();

    }
}
