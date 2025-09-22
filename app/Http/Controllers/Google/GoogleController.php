<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
use App\Models\Google;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Google_Service_Calendar;
use Google_Service_Oauth2;
use Google_Client;

class GoogleController extends Controller
{
    public function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URL'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Google_Service_Calendar::CALENDAR_EVENTS,
            Google_Service_Calendar::CALENDAR_READONLY,
            Google_Service_Oauth2::USERINFO_EMAIL,
            Google_Service_Oauth2::USERINFO_PROFILE,
        ]);
        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect()->route('dashboard')->with('errors', 'Failed to authenticate with Google.');
        }

        $client->setAccessToken($token);

        // Check if the token has the required calendar scopes
        $requiredScopes = [
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.readonly'
        ];

        $grantedScopes = explode(' ', $token['scope'] ?? '');
        $hasRequiredScopes = array_intersect($requiredScopes, $grantedScopes);

        if (empty($hasRequiredScopes)) {
            return redirect()->route('dashboard')->with('errors', 'Google Calendar permissions are required. Please grant calendar access to connect your Google Calendar.');
        }

        $googleService = new Google_Service_Oauth2($client);
        $userInfo = $googleService->userinfo->get();

        // Save token to user (in DB)
        Google::updateOrCreate(['user_id' => auth()->user()->id],
            [
                'access_token' => $token['access_token'],
                'expires_in' => $token['expires_in'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'scope' => $token['scope'],
                'token_created' => $token['created'],
                'token_type' => $token['token_type'],
                'id_token' => $token['id_token'],
                'name' => $userInfo->name,
                'picture' => $userInfo->picture,
                'email' => $userInfo->email,
                'refresh_token_expires_in' => $token['refresh_token_expires_in'] ?? null,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Google Calendar connected!');
    }
}
