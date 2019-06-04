<?php

namespace App\Http\Controllers;

use Google_Client;
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
    protected $accessTokenUrl = 'https://www.googleapis.com/oauth2/v4/token';

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
     * @throws \Google_Exception
     */
    public function getAccessToken(Request $request): array
    {
        Log::info("Google auth request", $request->toArray());
        $client = $this->getGoogleClient();
        $client->setRedirectUri($request->input('redirectUri'));
        $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));
        Log::info("Access token Result", $token);
        return $token;
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

    /**
     * @throws \Google_Exception
     */
    private function getGoogleClient()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('google-credentials.json'));
        $client->setScopes('email');
        return $client;
    }
}
