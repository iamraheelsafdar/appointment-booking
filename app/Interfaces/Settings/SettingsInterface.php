<?php

namespace App\Interfaces\Settings;

interface SettingsInterface
{
    public static function updateSettingView();

    public static function updateSetting($request);
}
