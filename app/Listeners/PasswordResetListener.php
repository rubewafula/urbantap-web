<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\PasswordResetEvent;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;

/**
 * Class PasswordResetListener
 * @package App\Listeners
 */
class PasswordResetListener implements ShouldSendSMS, ShouldSendMail
{
    use SendSMSTrait, SendEmailTrait, UserDataTrait;

    /**
     * @var string
     */
    private $userMailTemplate = "general.email.blade.html";

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param PasswordResetEvent $event
     * @return void
     */
    public function handle(PasswordResetEvent $event)
    {
        $data = $this->getUserNotificationData($event->user, $event->data);
        $this->send($data, $this->userMailTemplate);
        $this->sms(array_merge(Arr::get($data, 'sms'), Arr::only($data, 'message')));
    }


    /**
     * @param User $user
     * @param array $data
     * @return string
     */
    protected function getUserNotificationMessage(User $user, array $data): string
    {
        return "Hello " . $user->first_name . "," . PHP_EOL . PHP_EOL
            . " We have received request to reset your password " . PHP_EOL
            . " Click on the below link to reset you password " . PHP_EOL
            . env('APP_URL', 'https:urbantap.co.ke/') . "/account/verify/" . Arr::get($data, 'token_hash')
            . PHP_EOL
            . " If you did not request for passwrod reset please ignore this email. " . PHP_EOL . PHP_EOL
            . " Cheers " . PHP_EOL
            . " URBANTAP - Tap to Freedom ";
    }


}
