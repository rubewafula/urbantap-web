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
        $this->sms(Arr::get($data, 'sms'));
    }


    /**
     * @param User $user
     * @param array $data
     * @return string
     */
    protected function getUserNotificationMessage(User $user, array $data): string
    {
        return "Hello " . $user->first_name . "," . "<br/>" . "<br/>"
            . " We have received request to reset your password " . "<br/>"
            . " Click on the below link to reset you password " . "<br/>"
            . env('APP_URL', 'https:urbantap.co.ke/') . "/reset-password <br/>"
            . "Use the code " . Arr::get($data, 'token')
            . "<br/>"
            . " If you did not request for passwrod reset please ignore this email. " . "<br/>" . "<br/>"
            . " Cheers " . "<br/>"
            . config('app.name') . " - Tap to Freedom ";
    }
}
