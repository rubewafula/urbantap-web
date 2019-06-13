<?php


namespace App\Traits;


use App\ServiceProvider;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
        $provider = [];
        $id = Arr::get($data, 'request.service_provider_id', Arr::get($data, 'service_provider_id'));
        if ($id) {
            Log::info("Service provider exists", compact('id'));
            $provider = optional(ServiceProvider::first($id))->toArray() ?: [];
        }
        return array_merge(
            $data,
            $user->toArray(),
            [
                'to'                  => $email = $user->email,
                'email_address'       => $email,
                'msisdn'              => $msisdn = $user->phone_no,
                'subject'             => Arr::get($data, 'subject'),
                'reference'           => Arr::get($data, 'booking_id'),
                'user_id'             => $user->id,
                'service_provider_id' => Arr::get($data, 'request.service_provider_id', null),
                'message'             => $this->getUserNotificationMessage($user, $data),
                'sms'                 => [
                    'recipients' => $msisdn ? [$msisdn] : [],
                    'message'    => Arr::get($data, 'message')
                ]
            ],
            $provider
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