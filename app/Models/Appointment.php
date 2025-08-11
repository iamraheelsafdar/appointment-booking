<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];


    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'appointment_id', 'id');
    }
    public function coach(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'coach_id');
    }

}
