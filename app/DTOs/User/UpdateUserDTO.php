<?php

namespace App\DTOs\User;

use App\DTOs\BaseDTO;

class UpdateUserDTO extends BaseDTO
{

    public string $name;
    public string $phone;
    public string $status;
    public string $coach_type;

    public function __construct($request, $user)
    {
        $this->name = $request->name;
        $this->phone = $request->phone;
        $this->status = $user->remember_token == null ? ($request->status == '1' ? 1 : 0) : 0;
        $this->coach_type = $request->coach_type;
    }
}
