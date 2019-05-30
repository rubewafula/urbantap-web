<?php


namespace App\Traits;


use App\User;
use App\Utilities\RawQuery;
use Exception;
use Illuminate\Support\Facades\URL;

/**
 * Trait ProviderDataTrait
 * @package App\Traits
 */
trait ProviderDataTrait
{
    /**
     * @return string
     */
    protected function getProviderDataSelectStatement()
    {
        $sp_providers_url = URL::to('/storage/static/image/service-providers/');
        return "select sp.user_id, s.service_name, sp.service_provider_name, sp.instagram, sp.twitter, sp.facebook, sp.business_email, "
            . " sp.business_phone, sp.work_location_city, sp.business_description, sp.work_location, "
            . " concat( '$sp_providers_url' , '/', if(sp.cover_photo is null, 'img-03.jpg', "
            . " JSON_UNQUOTE(json_extract(sp.cover_photo, '$.media_url')))) as cover_photo, "
            . " s.service_name, ps.description as service_description ";
    }

    /**
     * @return string
     */
    protected function getProviderFromClause()
    {
        return " from service_providers sp inner join provider_services ps on "
            . " sp.id = ps.service_provider_id  inner join services s on s.id = ps.service_id "
            . "  where sp.id=:sp_id  and s.id =:service_id ";
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getProviderBindings(array $data): array
    {
        return [
            'sp_id'      => $data['request']['service_provider_id'],
            'service_id' => $data['request']['service_id'],
        ];
    }

    /**
     * Get provider data
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function getServiceProviderNotificationData(array $data): array
    {
        $sp = $this->queryData($data);
        return [
            array_merge(
                $data,
                [
                    'provider' => (array)$sp
                ]
            ),
            new User(['id' => $sp->user_id]),
            $this->getNotificationMessage($data)
        ];
    }

    /**
     * @param $data
     * @return \stdClass
     * @throws Exception
     */
    protected function queryData($data): \stdClass
    {
        $data = RawQuery::query(
            $this->getProviderDataSelectStatement() . $this->getProviderFromClause(),
            $this->getProviderBindings($data)
        );
        if (count($data)) {
            return $data[0];
        }
        throw new Exception("Failed to execute query");
    }

    abstract protected function getNotificationMessage(array $data): string;
}