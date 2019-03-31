<?php

use App\Email;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EmailTest extends TestCase
{

    /**
     * @return void
     */
    public function testPostSuccess()
    {
        $faker = Faker\Factory::create();
        $data = array (
            'to' =>  [$faker->email, $faker->email, $faker->email],
            'subject' =>  $faker->sentence(5),
            'content' => $faker->realText(200),
            'type' => Email::TYPE_TEXT_PLAIN
        );
        $response = $this->post('/email', $data);
        $response->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * @return void
     */
    public function testPostFail()
    {
        $faker = Faker\Factory::create();
        $data = array (
            'to' =>  $faker->email,
            'subject' =>  $faker->sentence(5),
            'content' => $faker->realText(200),
            'type' => 'text'
        );
        $this->json('POST', '/email', $data, [])
            ->seeJson([
                'errors' => [
                    'type' => ['The selected type is invalid.']
                ],
                'success' => false
            ]);
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $lastRow = DB::table('email')->orderBy('id', 'desc')->first();
        if (!empty($lastRow)) {
            $response = $this->get('/email/' . $lastRow->id);
            $response->seeStatusCode(Response::HTTP_OK);
        }

    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $lastRow = DB::table('email')->orderBy('id', 'desc')->first();
        if (!empty($lastRow)) {
            $response = $this->delete('/email/' . $lastRow->id);
            $response->seeStatusCode(Response::HTTP_NO_CONTENT);
        }

    }
}
