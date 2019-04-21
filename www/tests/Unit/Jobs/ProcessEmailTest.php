<?php

namespace Tests\Unit;

use App\Email;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\EmailRepository;
use App\Jobs\ProcessEmail;
use Mockery;

/**
 * Class EmailRepositoryTest
 * @package Tests\Unit
 */
class ProcessEmailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var EmailRepository */
    private $emailRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->emailRepository = new EmailRepository($this->app);
    }

    public function testProcessEmailDispatched()
    {
        Queue::fake();

        Queue::assertNothingPushed();

        $data = [
            'id' => 1,
            'to' =>  $this->faker->email,
            'subject' =>  $this->faker->sentence(5),
            'content' => $this->faker->realText(200),
            'type' => Email::TYPE_TEXT_PLAIN,
            'status' => Email::STATUS_QUEUED,
            'service' => null,
            'attempts' => 0,
            'created_at' => '2019-04-21 12:00:00',
            'updated_at' => '2019-04-21 12:00:00',
        ];
        $mockModel = Mockery::mock('App\Email');
        $mockModel->shouldReceive('getAttribute')->twice()->andReturn($data['id']);
        dispatch(new ProcessEmail($mockModel));

        Queue::assertPushed(ProcessEmail::class, function ($job) use ($mockModel) {
            $email = $job->getEmail();
            return $email->id === $mockModel->id;
        });
    }
}
