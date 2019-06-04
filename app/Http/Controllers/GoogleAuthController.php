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

    private $googleClient;

    /**
     * GoogleAuthController constructor.
     * @param Client $client
     * @throws \Google_Exception
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->googleClient = $this->getGoogleClient();
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
        $this->googleClient->setRedirectUri($request->input('redirectUri'));
        $token = $this->googleClient->fetchAccessTokenWithAuthCode($request->input('code'));
        Log::info("Access token Result", $token);
        return $token;
    }

    /**
     * Fetch user's profile
     *
     * @param string $token
     * @return array
     * @throws \Exception
     */
    public function getUserProfile(string $token = null): array
    {
        $profile = $this->googleClient->verifyIdToken();
        if (!$profile)
            throw new \Exception("User profile not found");
        Log::info("Google profile", $profile);
        return [
            'first_name' => Arr::get($profile, 'given_name'),
            'last_name'  => Arr::get($profile, 'family_name'),
            'picture'    => Arr::get($profile, 'picture'),
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
