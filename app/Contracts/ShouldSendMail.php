<?php


namespace App\Contracts;


/**
 * Interface ShouldSendMail
 * @package App\Contracts
 */
interface ShouldSendMail
{
    /**
     * Send email.
     * All data should be provided in the data array
     *
     * @param array $data
     * @param string $template
     */
    public function send(array $data, string $template);

}