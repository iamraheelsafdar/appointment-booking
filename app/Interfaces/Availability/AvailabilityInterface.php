<?php

namespace App\Interfaces\Availability;

interface AvailabilityInterface
{
    public static function availabilityView();

    public static function createAvailability($request);

}
