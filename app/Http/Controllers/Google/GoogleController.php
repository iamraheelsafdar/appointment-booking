<?php

namespace App\Http\Controllers\Google;

use App\Http\Controllers\Controller;
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
        $client->setRedirectUri('http://127.0.0.1:8000/google/callback');
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Oauth2::USERINFO_EMAIL,
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
            return redirect()->route('dashboard')->with('error', 'Failed to authenticate with Google.');
        }

        $client->setAccessToken($token);

        $googleService = new Google_Service_Oauth2($client);
        $userInfo = $googleService->userinfo->get();

        // Save token to user (in DB)
        $user = Auth::user();
        $user->google_access_token = json_encode($token);
        $user->google_email = $userInfo->email;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Google Calendar connected!');
    }
}
