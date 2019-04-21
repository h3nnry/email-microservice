<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Email;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;

class EmailTest extends TestCase
{
    use WithFaker;

    /**
     * @return void
     */
    public function testPostSuccess()
    {
        $data = array (
            'to' =>  [$this->faker->email, $this->faker->email, $this->faker->email],
            'subject' =>  $this->faker->sentence(5),
            'content' => $this->faker->realText(200),
            'type' => Email::TYPE_TEXT_PLAIN
        );
        $response = $this->post('/api/email', $data);
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * @return void
     */
    public function testPostFail()
    {
        $data = array (
            'to' =>  $this->faker->email,
            'subject' =>  $this->faker->sentence(5),
            'content' => $this->faker->realText(200),
            'type' => 'text'
        );


        $response = $this->post('/api/email', $data);
        $response->assertStatus(Response::HTTP_FOUND);
    }
}