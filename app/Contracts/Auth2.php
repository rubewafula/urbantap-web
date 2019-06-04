<?php


namespace App\Contracts;


use Illuminate\Http\Request;

/**
 * Interface Auth2
 * @package App\Contracts
 */
interface Auth2
{
    /**
     * Exchange authorization code for access token
     *
     * @param Request $request
     * @return array
     */
    public function getAccessToken(Request $request): array;

    /**
     * Fetch user's profile
     *
     * @param string $token
     * @return array
     */
    public function getUserProfile(string $token): array;
}