<?php

namespace Tests\Unit;

use App\Email;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\EmailRepository;

/**
 * Class EmailRepositoryTest
 * @package Tests\Unit
 */
class EmailRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var EmailRepository */
    private $emailRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->emailRepository = new EmailRepository($this->app);
    }

    public function testCreateEmail()
    {
        $data = [
            'to' =>  $this->faker->email,
            'subject' =>  $this->faker->sentence(5),
            'content' => $this->faker->realText(200),
            'type' => Email::TYPE_TEXT_PLAIN,
            'status' => Email::STATUS_QUEUED
        ];

        $email = $this->emailRepository->create($data);

        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals($data['to'], $email->to);
        $this->assertEquals($data['subject'], $email->subject);
        $this->assertEquals($data['content'], $email->content);
        $this->assertEquals($data['type'], $email->type);
        $this->assertEquals($data['status'], $email->status);
    }
}
