<?php

namespace App\Http\Resources\Appointment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetAppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'suburb' => $this->suburb,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'postal_code'=>$this->postal_code,
            'state' => $this->state,
            'appointment_status' => $this->appointment_status,
            'total_minutes' => $this->total_minutes,
            'total_amount' => $this->total_amount,
            'selected_date' => $this->selected_date,
            'selected_time_slot' => $this->selected_time_slot,
        ];
    }
}
