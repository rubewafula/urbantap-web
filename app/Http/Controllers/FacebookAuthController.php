<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class FacebookAuthController
 * @package App\Http\Controllers
 */
class FacebookAuthController extends Controller
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $accessTokenUrl = 'https://graph.facebook.com/v3.3/oauth/access_token';

    /**
     * FacebookAuthController constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->getAccessToken($request);
    }

    /**
     * @param Request $request
     */
    private function getAccessToken(Request $request)
    {
        Log::info("Facebook auth body", $request->toArray());
        $query = [
            'code'          => $request->get('code'),
            'client_id'     => $request->get('clientId'),
            'redirect_uri'  => $request->get('redirectId'),
            'client_secret' => config('services.facebook.secret')
        ];
        Log::info("Computed params for authorisation token", $query);
        $response = $this->client->get($this->accessTokenUrl, compact('query'));
        $accessToken = json_decode($response->getBody(), true);
        Log::info("Facebook Response", compact('accessToken'));
    }
}
