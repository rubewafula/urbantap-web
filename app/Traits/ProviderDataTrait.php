<?php


namespace App\Traits;


use App\User;
use App\Utilities\RawQuery;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Trait ProviderDataTrait
 * @package App\Traits
 * @deprecated
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
            . "  where sp.id=:sp_id  and ps.id =:service_id ";
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
	$sp = array_get($data, 'provider');
        $data = [
            array_merge(
                $data,
                [
                    'provider'      => $sp->toArray(),
                    'message'       => $this->getNotificationMessage($data),
                    'msisdn'        => $msisdn = $sp->{'business_phone'},
                    'email_address' => $sp->{'business_email'},
                    'sms'           => 
                        [
                            'recipients' => [$msisdn],
                            'message'    => Arr::get($data, 'message'),
                        ],
                ]
            ),
        ];
        Log::info("Final provider data", $data);
        return $data;
    }

    /**
     * @param $data
     * @return \stdClass
     * @throws Exception
     */
    protected function queryData($data): \stdClass
    {
        Log::info("Begin fetching provider information", $data);
        $bindings = $this->getProviderBindings($data);
        $query = $this->getProviderDataSelectStatement() . $this->getProviderFromClause();
        Log::info("Query bindings", $bindings);
        Log::info("Provider raw query", compact('query'));
        $data = DB::select(
            $query,
            $bindings
        );
        Log::info("Query result", $data);
        if (count($data)) {
            return $data[0];
        }
        Log::error("No provider found", $bindings);
        throw new Exception("Failed to execute query");
    }

    abstract protected function getNotificationMessage(array $data): ?string;
}
