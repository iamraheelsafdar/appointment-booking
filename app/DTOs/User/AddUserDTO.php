<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddUserDTO extends BaseDTO
{
    public mixed $name;
    public mixed $user_type;
    public mixed $email;
    public mixed $password;
    public mixed $remember_token;


    public function __construct($request)
    {
        $this->name = $request['name'];
        $this->user_type = 'User';
        $this->email = $request['email'];
        $this->password = Hash::make(Str::uuid()->toString());
        $this->remember_token = Str::uuid()->toString();
    }
}
