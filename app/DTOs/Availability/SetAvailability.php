<?php

namespace App\DTOs\Availability;

use App\DTOs\BaseDTO;

class SetAvailability extends BaseDTO
{
    public mixed $day;
    public mixed $start_time;
    public mixed $end_time;
    public mixed $availability;

    public function __construct($day, $startTime, $endTime)
    {
        $this->start_time = $day ? ($startTime == null ? '12:00' : $startTime) : null;
        $this->end_time = $day ? ($endTime == null ? '12:00' : $endTime) : null;
        $this->availability = $day ? 1 : 0;
    }
}
