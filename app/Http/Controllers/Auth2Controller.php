<?php


namespace App\Http\Controllers;


use App\Contracts\Auth2;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class Auth2Controller
 * @package App\Http\Controllers
 */
abstract class Auth2Controller extends Controller implements Auth2
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://graph.facebook.com/v3.3/oauth/access_token';
    /**
     * @var string
     */
    protected $profileUrl = 'https://graph.facebook.com/v3.3/me';

    /**
     * Auth2Controller constructor.
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
        $token = $this->getAccessToken($request);
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
}