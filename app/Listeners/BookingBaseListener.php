<?php


namespace App\Listeners;


use App\Contracts\ShouldSendMail;
use App\Contracts\ShouldSendSMS;
use App\Traits\ProviderDataTrait;
use App\Traits\SendEmailTrait;
use App\Traits\SendSMSTrait;
use App\Traits\UserDataTrait;

/**
 * Class BookingBaseListener
 * @package App\Listeners
 */
abstract class BookingBaseListener implements ShouldSendMail, ShouldSendSMS
{
    use SendEmailTrait, SendSMSTrait, ProviderDataTrait, UserDataTrait;

    /**
     * @return string
     */
    protected function getProviderFromClause()
    {
        return " from service_providers sp inner join provider_services ps on "
            . " sp.id = ps.service_provider_id  inner join services s on s.id = ps.service_id "
            . " inner join bookings b on b.provider_service_id = ps.id "
            . "  where b.id = :booking_id ";
    }

    /**
     * Query bindings
     *
     * @param array $data
     * @return array
     */
    protected function getProviderBindings(array $data): array
    {
        return [
            'booking_id' => $data['booking_id']
        ];
    }
}