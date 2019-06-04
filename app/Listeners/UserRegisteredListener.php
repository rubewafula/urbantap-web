<?php

namespace App\Listeners;

use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Events\UserRegistered;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;
use App\User;
use Illuminate\Support\Arr;

/**
 * Class UserRegisteredListener
 * @package App\Listeners
 */
class UserRegisteredListener implements ShouldSendMail, ShouldSendSMS
{
    use UserDataTrait, SendEmailTrait, SendSMSTrait;
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
     * @param UserRegistered $event
     * @return void
     */
    public function handle(UserRegistered $event)
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
        return "Dear " . $user->first_name . "," . PHP_EOL . PHP_EOL
            . " Thank you for signing up with URBANTAP. From now on you will be able to order for our services on the fly. Feel free to peruse through the profiles on URBANTAP and identify the best service providers you can order from. " . PHP_EOL . PHP_EOL
            . " Click on the below link to get your account verified and start tapping to freedom " . PHP_EOL
            . env('APP_URL', 'http:127.0.0.1:8000/') . "/account/verify/" . Arr::get($data, 'token_hash') . " " . PHP_EOL . PHP_EOL
            . " Cheers " . PHP_EOL
            . " URBANTAP - Tap to Freedom ";
    }


}
