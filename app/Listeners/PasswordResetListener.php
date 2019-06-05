<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\PasswordResetEvent;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;
use App\User;
use Illuminate\Support\Arr;

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
        return "Hello " . $user->first_name . "," . "\n" . "\n"
            . " We have received request to reset your password " . "\n"
            . " Click on the below link to reset you password " . "\n"
            . env('APP_URL', 'https:urbantap.co.ke/') . "/account/verify/" . Arr::get($data, 'token_hash')
            . "\n"
            . " If you did not request for passwrod reset please ignore this email. " . "\n" . "\n"
            . " Cheers " . "\n"
            . " URBANTAP - Tap to Freedom ";
    }


}
