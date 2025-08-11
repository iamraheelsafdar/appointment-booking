<?php

namespace App\Interfaces\Appointment;

interface AppointmentInterface
{
    public static function appointmentsView($request);

    public static function updateAppointmentsView($id);

    public static function updateAppointments($request);

}
