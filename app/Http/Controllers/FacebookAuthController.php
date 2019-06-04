<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class FacebookAuthController
 * @package App\Http\Controllers
 */
class FacebookAuthController extends Auth2Controller
{
    /**
     * FacebookAuthController constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    /**
     * @param string $token
     * @return array
     */
    public function getUserProfile(string $token = null): array
    {
        $fields = 'id,email,first_name,last_name,link,name';
        $response = $this->client->get($this->profileUrl, [
            'query' => [
                'access_token' => $token,
                'fields'       => $fields
            ]
        ]);
        $profile = json_decode($response->getBody(), true);
        Log::info("Facebook user profile", $profile);
        return $profile;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAccessToken(Request $request): array
    {
        $query = [
            'code'          => $request->code,
            'client_id'     => $request->clientId,
            'redirect_uri'  => $request->redirectUri,
            'client_secret' => config('services.facebook.secret')
        ];
        Log::info("Computed params for authorisation token", $query);
        $response = $this->client->get($this->accessTokenUrl, compact('query'));
        $accessToken = json_decode($response->getBody(), true);
        Log::info("Facebook Response", $accessToken);
        return $accessToken;
    }
}
