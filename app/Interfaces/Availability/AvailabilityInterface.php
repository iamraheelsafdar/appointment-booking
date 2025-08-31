<?php

namespace App\Interfaces\Availability;

interface AvailabilityInterface
{
    public static function availabilityView($id);

    public static function createAvailability($request);
    public static function deleteAvailability($request);

}
