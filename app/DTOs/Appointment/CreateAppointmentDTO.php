<?php

namespace App\DTOs\Appointment;

use App\DTOs\BaseDTO;
use Illuminate\Support\Str;

class CreateAppointmentDTO extends BaseDTO
{
    public mixed $coach_id;
    public mixed $name;
    public mixed $email;
    public mixed $suburb;
    public mixed $address;
    public mixed $city;
    public mixed $country;
    public mixed $postal_code;
    public mixed $player_type;
    public mixed $state;
    public mixed $total_minutes;
    public mixed $total_amount;
    public mixed $selected_time_slot;
    public mixed $selected_date;
    public mixed $booking_id;

    public function __construct($request, $coach)
    {
        if ($coach && $coach->id != null) {
            $this->coach_id = $coach->id;
        }
        $this->name = $request->fullName;
        $this->email = $request->email;
        $this->suburb = $request->suburb;
        $this->address = $request->address;
        $this->city = $request->city;
        $this->country = $request->country;
        $this->postal_code = $request->postalCode;
        $this->player_type = $request->playerType;
        $this->state = $request->state;
        $this->total_minutes = $request->totalMinutes;
        $this->total_amount = $request->totalAmount;
        $this->selected_time_slot = $request->selectedTimeSlot;
        $this->selected_date = $request->selectedDate;
        $this->booking_id = Str::uuid()->toString();
    }
}
