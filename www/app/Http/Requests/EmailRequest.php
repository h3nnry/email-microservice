<?php

namespace App\Http\Requests;

use App\Email;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class EmailRequest
 * @package App\Http\Requests
 */
class EmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        $data = parent::validationData();
        (!empty($data['to']) && is_string($data['to'])) && $data['to'] = explode(',', $data['to']);

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:' . implode(',', Email::mailTypes()),
            'to.*' => 'required|email'
        ];
    }
}
