<?php

namespace App\DTOs;

class BaseDTO
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this;
    }
}
