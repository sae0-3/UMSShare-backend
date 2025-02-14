<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;

class GoogleController extends Controller
{
    public function getClient(): Google_Client
    {
        $client = new Google_Client();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setPrompt('select_account');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setScopes([
            \Google\Service\Oauth2::USERINFO_PROFILE,
            \Google\Service\Oauth2::USERINFO_EMAIL,
            \Google\Service\Oauth2::OPENID,
            \Google\Service\Drive::DRIVE
        ]);
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    public function getAuthUrl(): JsonResponse
    {
        $client = $this->getClient();

        $authUrl = $client->createAuthUrl();

        return response()->json([
            'url' => $authUrl,
        ], 200);
    }

    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                $user->update([
                    'google_token' => $googleUser->token,
                ]);
            }

            $token = $user->createToken('Google Auth')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google Auth Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Unable to authenticate user.',
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }
}
