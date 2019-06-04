<?php


namespace App\Traits;


use App\User;
use Illuminate\Support\Arr;

/**
 * Trait UserDataTrait
 * @package App\Traits
 */
trait UserDataTrait
{

    /**
     * User notification data
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    protected function getUserNotificationData(User $user, array $data)
    {
        return array_merge(
            $data,
            $user->toArray(),
            [
                'to'                  => $email = $user->email,
                'email_address'               => $email,
                'msisdn'              => $msisdn = $user->phone_no,
                'subject'             => Arr::get($data, 'subject'),
                'reference'           => Arr::get($data, 'booking_id'),
                'user_id'             => $user->id,
                'service_provider_id' => Arr::get($data, 'request.service_provider_id', null),
                'message'             => $message = $this->getUserNotificationMessage($user, $data),
                'sms'                 => [
                    'recipients' => $msisdn ? [$msisdn] : [],
                    'message'    => $message
                ]
            ]
        );
    }

    /**
     * Get user's notification message
     *
     * @param User $user
     * @param array $data
     * @return string
     */
    protected function getUserNotificationMessage(User $user, array $data): string
    {
        return "";
    }
}