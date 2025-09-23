<?php

namespace App;

use App\Models\Google;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\DTOs\RequestHandling\ErrorLogsDTO;
use Illuminate\Http\Response;
use App\Models\ErrorLog;

class Helper
{
    /**
     * @param $request
     * @param $e
     * @param $functionName
     * @return Response
     */
    public static function errorHandling($request, $e, $functionName): Response
    {
        ErrorLog::create((new ErrorLogsDTO($request, $e, $functionName))->toArray());
        return response()->view('error', ['error' => $e], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param $request
     * @param $e
     * @param $functionName
     * @return JsonResponse
     */
    public static function jsonErrorHandling($request, $e, $functionName): JsonResponse
    {
        ErrorLog::create((new ErrorLogsDTO($request, $e, $functionName))->toArray());
        return response()->json(['errors' => ['Something went wrong please try again']], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param $timeRangeString
     * @param int $addExtraMinutes
     * @return array
     */
    public static function extractTimeSlots($timeRangeString,$addExtraMinutes=0, $value=0): array
    {
        // Example: "3:15 PM - 4:15 PM, Tue, Aug 5, 2025"
        [$timePart, $datePart] = explode(',', $timeRangeString, 2);
        [$start, $end] = explode(' - ', trim($timePart));

        $date = trim(explode(',', $datePart)[1]); // Get "Aug 5, 2025"
        $dateObj = Carbon::parse($date);

        $startTime = Carbon::parse($date . ' ' . $start);
        $endTime = Carbon::parse($date . ' ' . $end)->addMinutes($value);

        // Generate 15-minute intervals
        $period = CarbonPeriod::create($startTime, $addExtraMinutes.' minutes', $endTime);
        $slots = [];

        foreach ($period as $time) {
            $slots[] = $time->format('g:i A');
        }

        return $slots;
    }

    /**
     * @param $user
     * @return Google_Client|string
     */
    public static function getGoogleClientForUser($user): Google_Client|string
    {
        $tokenData = Google::where('user_id', $user->id)->first();

        if (!$tokenData) {
            return 'Google Calendar is not accessible. Please login to your calendar again.';
        }

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
            'access_token' => $tokenData->access_token,
            'refresh_token' => $tokenData->refresh_token,
            'expires_in' => $tokenData->expires_in,
            'token_created' => strtotime($tokenData->updated_at),
        ]);

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            try {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                
                if (isset($newToken['error'])) {
                    \Log::error('Google token refresh failed: ' . json_encode($newToken));
                    return 'Google Calendar is not accessible. Please login to your calendar again.';
                }
                
                $tokenData->update([
                    'access_token' => $newToken['access_token'],
                    'expires_in' => $newToken['expires_in'],
                ]);
                $client->setAccessToken($newToken);
            } catch (\Exception $e) {
                \Log::error('Google token refresh exception: ' . $e->getMessage());
                return 'Google Calendar is not accessible. Please login to your calendar again.';
            }
        }

        // Check if the token has the required scopes
        $requiredScopes = [
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.readonly'
        ];

        $tokenScopes = explode(' ', $tokenData->scope ?? '');
        $hasRequiredScopes = array_intersect($requiredScopes, $tokenScopes);

        if (empty($hasRequiredScopes)) {
            // Token doesn't have required scopes, need to re-authenticate
            return 'Google Calendar is not accessible. Please login to your calendar again.';
        }

        return $client;
    }

    /**
     * @param $user
     * @param $googleEventId
     * @param $request
     * @return void
     * @throws Exception
     */
    public static function removeBooking($user, $googleEventId, $request): void
    {
        try {
            $client = Helper::getGoogleClientForUser($user);
            
            // Check if client is a string (error message) instead of Google_Client object
            if (is_string($client)) {
                \Log::warning("Cannot remove Google Calendar event for user {$user->id}: {$client}");
                return;
            }
            
            $calendarService = new Google_Service_Calendar($client);
            $calendarService->events->delete('primary', $googleEventId);
            
            \Log::info("Successfully removed Google Calendar event {$googleEventId} for user {$user->id}");
        } catch (\Google_Service_Exception $e) {
            // Log the error but don't throw it to prevent breaking the appointment update
            \Log::warning("Failed to remove Google Calendar event {$googleEventId} for user {$user->id}: " . $e->getMessage());
        } catch (\Exception $e) {
            \Log::warning("Unexpected error removing Google Calendar event {$googleEventId} for user {$user->id}: " . $e->getMessage());
        }
    }
}
