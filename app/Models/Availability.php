<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    private static function getDayNumber($dayName)
    {
        $days = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 0,
        ];

        return $days[$dayName] ?? 0;
    }

    public static function getMergedAvailability()
    {
        $availabilities = self::where('is_active', true)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        $finalAvailability = [];

        foreach ($availabilities as $day => $dayAvailabilities) {
            $chunks = [];

            foreach ($dayAvailabilities as $availability) {
                $chunks[] = [
                    'startTime' => $availability->start_time->format('H:i'),
                    'endTime'   => $availability->end_time->format('H:i'),
                ];
            }

            // Remove exact duplicates (same start and end)
            $uniqueChunks = self::removeExactDuplicates($chunks);

            // Convert day name to number (0 = Sunday, 1 = Monday, etc.)
            $dayNumber = self::getDayNumber($day);

            $finalAvailability[$dayNumber] = [
                'chunks' => $uniqueChunks
            ];
        }

        return $finalAvailability;
    }

    private static function removeExactDuplicates($chunks)
    {
        $unique = [];
        $seen = [];

        foreach ($chunks as $chunk) {
            $key = $chunk['startTime'] . '-' . $chunk['endTime'];

            if (!isset($seen[$key])) {
                $unique[] = $chunk;
                $seen[$key] = true;
            }
        }

        return $unique;
    }
}
