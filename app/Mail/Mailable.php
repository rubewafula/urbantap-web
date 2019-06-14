<?php


namespace App\Mail;


/**
 * Class Mailable
 * @package App\Mail
 */
abstract class Mailable extends \Illuminate\Mail\Mailable
{
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
}