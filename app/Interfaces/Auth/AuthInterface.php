<?php

namespace App\Interfaces\Auth;

interface AuthInterface
{
    public static function loginView();

    public static function login($request);

    public static function logout($request);

    public static function setPasswordView($email, $token);

    public static function setPassword($request);
}
