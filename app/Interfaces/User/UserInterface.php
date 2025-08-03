<?php

namespace App\Interfaces\User;

interface UserInterface
{
    public static function registerView();

    public static function register($request);

    public static function getUser($request);

    public static function deleteUser($request);

    public static function updateUserView($id);

    public static function updateUser($request);

    public static function updateProfileView();

    public static function updateProfile($request);
}
