<?php

namespace App\DTOs\Lessons;

use App\DTOs\BaseDTO;

class CreateLessonsDTO extends BaseDTO
{
    public string $appointment_id;
    public string $ball_level;
    public string $duration;
    public string $total_players;
    public string $type;

    public function __construct($id, $lessons)
    {
        $this->appointment_id = $id;
        $this->ball_level = $lessons['ballLevel'];
        $this->duration = $lessons['duration'];
        $this->total_players = $lessons['players'];
        $this->type = $lessons['type'];
    }
}
