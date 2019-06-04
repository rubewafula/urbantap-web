<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class GoogleAuthController
 * @package App\Http\Controllers
 */
class GoogleAuthController extends Auth2Controller
{
    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://accounts.google.com/o/oauth2/token';

    /**
     * @var string
     */
    protected $profileUrl = 'https://www.googleapis.com/plus/v1/people/me/openIdConnect';

    /**
     * GoogleAuthController constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    /**
     * Exchange authorization code for access token
     *
     * @param Request $request
     * @return array
     */
    public function getAccessToken(Request $request): array
    {
        Log::info("Google auth request", $request->toArray());
        $params = [
            'code'          => $request->input('code'),
            'client_id'     => $request->input('clientId'),
            'client_secret' => config('services.google.secret'),
            'redirect_uri'  => $request->input('redirectUri'),
            'grant_type'    => 'authorization_code',
        ];
        Log::info("Google auth form params", $params);
        $response = $this->client->post($this->accessTokenUrl, ['form_params' => $params]);
        $accessReponse = json_decode($response->getBody(), true);
        Log::info("Access token response", $accessReponse);
        return $accessReponse;

    }

    /**
     * Fetch user's profile
     *
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserProfile(string $token): array
    {
        $profileResponse = $this->client->request('GET', $this->profileUrl, [
            'headers' => array('Authorization' => 'Bearer ' . $token)
        ]);
        $profile = json_decode($profileResponse->getBody(), true);
        Log::info("Google profile", $profile);
        return [
            'first_name' => Arr::get($profile, 'name'),
            'email'      => Arr::get($profile, 'email')
        ];
    }
}
