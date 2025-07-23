<?php

namespace App\Interfaces\User;

interface UserInterface
{
    public static function registerView();
    public static function register($request);
    public static function getUser($request);
}
