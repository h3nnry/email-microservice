<?php

namespace App\Http\Controllers;

use App\Email;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {

        $result = Email::createNew($request->all());

        if (!empty($result['errors'])) {
            return response()->json(['success' => false, 'errors' => $result['errors']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['success' => true, 'result' => $result['results']]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {
        return response()->json(['result' => Email::find($id)]);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function delete($id)
    {
        $email = Email::find($id);
        if (!$email) {
            throw new \Exception('Email not found');
        }
        $email->delete();
        return response()->json('', Response::HTTP_NO_CONTENT);
    }

}
