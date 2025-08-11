<?php

namespace App\Jobs;

use App\Mail\Appointment\BookingReceivingMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;

class AppointmentReceivingjob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public mixed $name;
    public mixed $email;
    public mixed $descritption;
    public function __construct($name, $email, $descritption)
    {
        $this->name = $name;
        $this->email = $email;
        $this->descritption = $descritption;
        $this->onQueue('receiving-booking');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new BookingReceivingMail($this->name, $this->descritption));
    }
}
