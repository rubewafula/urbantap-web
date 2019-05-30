<?php


namespace App\Contracts;


/**
 * Interface ShouldSendSMS
 * @package App\Contracts
 */
interface ShouldSendSMS
{
    /**
     * @param array $data
     */
    public function sms(array $data);
}