<?php

namespace Tests\Unit;

use App\Email;
use App\Http\Controllers\EmailController;
use App\Http\Requests\EmailRequest;
use App\Jobs\ProcessEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\EmailRepository;
use Mockery;

/**
 * Class EmailControllerTest
 * @package Tests\Unit
 */
class EmailControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @var EmailRepository */
    private $emailRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->emailRepository = $this->createMock(EmailRepository::class);
    }

    public function testGet()
    {
        $mockRepository = Mockery::mock('App\Repositories\EmailRepository');
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
        $mockRepository->shouldReceive('find')->once()->andReturn($data);
        $controller = new EmailController($mockRepository);
        $request = EmailRequest::create("/api/email/{$data['id']}", 'GET',$data);
        $response = $controller->get($request);
        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);

    }

    public function testPost()
    {
        $mockRepository = Mockery::mock('App\Repositories\EmailRepository');
        $mockRequest = Mockery::mock('App\Http\Requests\EmailRequest');
        $mockModel = Mockery::mock('App\Email');
        $data = [
            'to' =>  [$this->faker->email],
            'subject' =>  $this->faker->sentence(5),
            'content' => $this->faker->realText(200),
            'type' => Email::TYPE_TEXT_PLAIN,
            'status' => Email::STATUS_QUEUED,
        ];
        $mockRepository->shouldReceive('create')->once()->andReturn($mockModel);
        $mockRequest->shouldReceive('validated')->once()->andReturn($data);
        $mockModel->shouldReceive('getAttribute')->once()->andReturn(1);
        $this->expectsJobs(ProcessEmail::class);
        $controller = new EmailController($mockRepository);
        $response = $controller->post($mockRequest);
        $this->assertEquals($response->getStatusCode(), Response::HTTP_OK);
        $this->assertEquals(['success' => true, 'result' => [1]], $response->getData(true));
    }

    public function testDelete()
    {
        $mockRepository = Mockery::mock('App\Repositories\EmailRepository');
        $mockRequest = Mockery::mock('App\Http\Requests\EmailRequest');
        $mockModel = Mockery::mock('App\Email');
        $mockRepository->shouldReceive('find')->once()->andReturn($mockModel);
        $mockRepository->shouldReceive('delete')->once()->andReturn(true);
        $controller = new EmailController($mockRepository);
        $response = $controller->delete($mockRequest);
        $this->assertEquals($response->getStatusCode(), Response::HTTP_NO_CONTENT);
    }
}