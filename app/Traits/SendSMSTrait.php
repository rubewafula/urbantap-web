<?php


namespace App\Traits;


use App\Utilities\RabbitMQ;
use Illuminate\Support\Arr;

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
        if (count(Arr::get($data, 'recipients', [])) )
            (new RabbitMQ())->publish($data, env('SMS_MESSAGE_QUEUE'), env('SMS_MESSAGE_EXCHANGE'), env('SMS_MESSAGE_ROUTE'));
    }
}