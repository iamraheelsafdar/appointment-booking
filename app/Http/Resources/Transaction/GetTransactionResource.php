<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetTransactionResource extends JsonResource
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
            'session_id' => $this->session_id,
            'type' => strtoupper($this->type),
            'status' => strtoupper($this->status),
            'currency' => strtoupper($this->currency),
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
