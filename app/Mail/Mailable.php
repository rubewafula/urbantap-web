<?php


namespace App\Mail;


use Illuminate\Queue\SerializesModels;

/**
 * Class Mailable
 * @package App\Mail
 */
abstract class Mailable extends \Illuminate\Mail\Mailable
{
    use SerializesModels;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    abstract function getMailTemplate(): string;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown($this->getMailTemplate(), $this->data);
    }
}