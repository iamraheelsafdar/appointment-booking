<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Google;
use App\Models\User;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;

class RefreshGoogleCalendarAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:refresh-auth {user_id? : The user ID to refresh auth for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Google Calendar authentication and tokens for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $this->checkAndNotifyUser($user);
        } else {
            $this->info('Checking all users with Google Calendar connections...');
            $users = User::whereHas('google')->get();
            
            foreach ($users as $user) {
                $this->checkAndNotifyUser($user);
            }
        }
        
        $this->info('Google Calendar auth check completed.');
        return 0;
    }
    
    private function checkAndNotifyUser($user)
    {
        $googleData = $user->google;
        if (!$googleData) {
            return;
        }
        
        $requiredScopes = [
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.readonly'
        ];
        
        $tokenScopes = explode(' ', $googleData->scope ?? '');
        $hasRequiredScopes = array_intersect($requiredScopes, $tokenScopes);
        
        if (empty($hasRequiredScopes)) {
            $this->warn("User {$user->name} (ID: {$user->id}) needs to re-authenticate Google Calendar.");
            $this->line("   Current scopes: " . ($googleData->scope ?: 'none'));
            $this->line("   Required scopes: " . implode(', ', $requiredScopes));
            $this->line("   Action: User should visit: " . route('redirectToGoogle'));
        } else {
            $this->info("User {$user->name} (ID: {$user->id}) has correct Google Calendar scopes.");
            
            // Try to refresh token if it's expired
            try {
                $client = new Google_Client();
                $client->setClientId(env('GOOGLE_CLIENT_ID'));
                $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
                $client->setScopes([
                    Google_Service_Calendar::CALENDAR_EVENTS,
                    Google_Service_Calendar::CALENDAR_READONLY,
                    Google_Service_Oauth2::USERINFO_EMAIL,
                    Google_Service_Oauth2::USERINFO_PROFILE,
                ]);
                $client->setAccessToken([
                    'access_token' => $googleData->access_token,
                    'refresh_token' => $googleData->refresh_token,
                    'expires_in' => $googleData->expires_in,
                    'token_created' => strtotime($googleData->updated_at),
                ]);
                
                if ($client->isAccessTokenExpired()) {
                    $this->info("  Token expired, refreshing...");
                    
                    if (!$googleData->refresh_token) {
                        $this->error("  No refresh token available for user {$user->id}");
                        return;
                    }
                    
                    $newToken = $client->fetchAccessTokenWithRefreshToken($googleData->refresh_token);
                    
                    if (isset($newToken['error'])) {
                        $this->error("  Token refresh failed: " . ($newToken['error_description'] ?? $newToken['error']));
                        return;
                    }
                    
                    // Update token in database
                    $googleData->update([
                        'access_token' => $newToken['access_token'],
                        'expires_in' => $newToken['expires_in'],
                    ]);
                    
                    $this->info("  Token refreshed successfully");
                } else {
                    $this->info("  Token is still valid");
                }
                
            } catch (\Exception $e) {
                $this->error("  Exception: " . $e->getMessage());
            }
        }
    }
}
