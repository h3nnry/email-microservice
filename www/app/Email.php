<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 * @package App
 */
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
        'to', 'subject', 'content', 'type', 'status'
    ];

    protected $guarded = [];

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
     * @return array
     */
    public static function mailProviders()
    {
        return [
            self::MAIL_PROVIDER_SENDGRID,
            self::MAIL_PROVIDER_MAILJET,
        ];
    }

}
