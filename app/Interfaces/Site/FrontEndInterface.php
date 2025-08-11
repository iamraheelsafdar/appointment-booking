<?php

namespace App\Interfaces\Site;

interface FrontEndInterface
{
    public static function frontendView();

    public static function bookAppointment($request);

    public static function handleWebhook($request);

}
