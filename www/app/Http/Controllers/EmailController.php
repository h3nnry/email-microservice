<?php

namespace App\Http\Controllers;

use App\Repositories\EmailRepository;
use App\Email;
use App\Http\Requests\EmailRequest;
use App\Jobs\ProcessEmail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;

/**
 * Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller
{

    /** @var EmailRepository */
    private $emailRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @param EmailRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(EmailRequest $request)
    {
        $validated = $request->validated();
        $saveData = Arr::only($validated, ['subject', 'content', 'type']);
        $saveData['status'] = Email::STATUS_QUEUED;

        $result = [];
        foreach ($validated['to'] as $to) {
            $saveData['to'] = $to;
            $email = $this->emailRepository->create($saveData);
            $result[] = $email->id;
            dispatch(new ProcessEmail($email));
        }

        return response()->json(['success' => true, 'result' => $result]);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {
        return response()->json(['result' => $this->emailRepository->find($id)]);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        $email = $this->emailRepository->find($id);
        if (!$email) {
            throw new \Exception('Email not found');
        }
        $this->emailRepository->delete($id);
        return response()->json('', Response::HTTP_NO_CONTENT);
    }

}
