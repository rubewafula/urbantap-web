<?php

namespace App\Http\Controllers;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
     * @var string
     */
    private $profileUrl = 'https://graph.facebook.com/v3.3/me';

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
     * @return array
     */
    public function store(Request $request)
    {
        Log::info("Facebook auth body", $request->toArray());
        $token = $this->getAccessToken($request->code);
        $profile = $this->getUserProfile(Arr::get($token, 'access_token'));
        $user = User::query()->firstOrCreate(Arr::only($profile, ['email']), array_merge(
            Arr::except($profile, ['id', 'name']),
            [
                'password' => ''
            ]
        ));
        if (!$user->verified) {
            Log::info("Verify account", $user->toArray());
            $user->update(['verified' => true]);
        }
        return [
            'access_token' => $user->createToken("Personal")->accessToken,
            'success'      => true,
            'user'         => $user,
            'user_details' => $user->details
        ];
    }

    /**
     * @param string $token
     * @return array
     */
    private function getUserProfile(string $token): array
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
     * @param string $code
     * @return array
     */
    private function getAccessToken(string $code): array
    {
        $query = [
            'code'          => $code,
            'client_id'     => config('services.facebook.client_id'),
            'redirect_uri'  => config('services.facebook.redirect_uri'),
            'client_secret' => config('services.facebook.secret')
        ];
        Log::info("Computed params for authorisation token", $query);
        $response = $this->client->get($this->accessTokenUrl, compact('query'));
        $accessToken = json_decode($response->getBody(), true);
        Log::info("Facebook Response", $accessToken);
        return $accessToken;
    }
}
