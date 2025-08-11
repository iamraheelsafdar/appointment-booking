<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetUserResource extends JsonResource
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
            'status' => $this->status == 1 ? 'active' : 'inactive',
            'last_login' => $this->last_login ? Carbon::parse($this->last_login)->diffForHumans() : '-',
            'registration_date' => $this->created_at->format('d-m-Y'),
            'phone' => $this->phone,
            'coach_type' => $this->coach_type,
        ];
    }
}
