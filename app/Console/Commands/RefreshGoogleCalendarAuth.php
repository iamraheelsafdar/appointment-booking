<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Google;
use App\Models\User;

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
    protected $description = 'Refresh Google Calendar authentication for users with old scopes';

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
        }
    }
}
