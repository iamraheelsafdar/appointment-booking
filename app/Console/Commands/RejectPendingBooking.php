<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RejectPendingBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reject-pending-booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use to reject all the pending appointment after 30 min';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Appointment::where('appointment_status', 'Pending')
            ->where('created_at', '<', Carbon::now()->subMinutes(30))
            ->update(['appointment_status' => 'Declined']);
    }
}
