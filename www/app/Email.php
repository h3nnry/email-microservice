<?php

namespace App;

use App\Jobs\ProcessEmail;
use Illuminate\Database\Eloquent\Model;


class Email extends Model
{
    const TYPE_TEXT_PLAIN = 'text/plain';
    const TYPE_TEXT_MARKDOWN = 'text/markdown';
    const TYPE_TEXT_HTML = 'text/html';

    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_QUEUED = 'queued';

    const MAIL_PROVIDER_MAILJET = 'mailjet';
    const MAIL_PROVIDER_SENDGRID = 'sendgrid';

    /**
     * @var string
     */
    protected $table = 'email';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'to', 'subject', 'content', 'type',
    ];

    protected $guarded = ['status'];

    /**
     * @return array
     */
    public static function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:' . implode(', ', self::mailTypes()),
        ];
    }

    /**
     * @return array
     */
    public static function mailTypes()
    {
        return [
            self::TYPE_TEXT_PLAIN,
            self::TYPE_TEXT_MARKDOWN,
            self::TYPE_TEXT_HTML
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function createNew($data)
    {
        $rules = self::rules();

        !is_array($data['to']) &&  $data['to'] = explode(',', $data['to']);
        if (!empty($data['to'])) {
            foreach ($data['to'] as $key => $val) {
                $rules['to.'.$key] = 'email|max:255';
            }
        } else {
            $rules['to'] = 'required';
        }

        $validator = \validator($data, $rules);

        $result = [
            'results' => [],
            'errors' => [],
        ];
        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            \Log::error('New records was not inserted! Next errors encountered: ' . json_encode($validator->errors()->getMessages()));
            $result['errors'] = $errors;
        } else {
            foreach ($data['to'] as $to) {
                $email = new self();
                $email->fill($data);
                $email->to = $to;
                $email->status = self::STATUS_QUEUED;
                $email->save();
                $result['results'][] = $email->id;
                dispatch(new ProcessEmail($email));
                \Log::info('Successfully inserted new record into email table');
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function mailProviders()
    {
        return [
            self::MAIL_PROVIDER_MAILJET,
            self::MAIL_PROVIDER_SENDGRID,
        ];
    }

}
