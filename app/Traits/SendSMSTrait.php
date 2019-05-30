<?php


namespace App\Traits;


use App\Utilities\RabbitMQ;

/**
 * Trait SendSMSTrait
 * @package App\Traits
 */
trait SendSMSTrait
{
    /**
     * @param array $data
     */
    public function sms(array $data)
    {
        (new RabbitMQ())->publish($data, env('SMS_MESSAGE_QUEUE'), env('SMS_MESSAGE_EXCHANGE'), env('SMS_MESSAGE_ROUTE'));
    }
}